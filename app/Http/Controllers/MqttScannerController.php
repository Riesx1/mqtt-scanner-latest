<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Laravel HTTP client
use Illuminate\Support\Facades\Log;
use App\Models\MqttScanHistory;
use App\Models\MqttScanResult;
use Illuminate\Support\Facades\DB;

class MqttScannerController extends Controller
{
    // Show dashboard (loads without blocking on Flask API)
    public function index()
    {
        // Get recent scan history for the authenticated user
        $recentScans = MqttScanHistory::where('user_id', auth()->id())
            ->with('results')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get latest scan results for display
        $latestResults = MqttScanResult::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('dashboard', compact('recentScans', 'latestResults'));
    }

    // Laravel server-side endpoint: calls Flask /api/scan
    public function scan(Request $request)
    {
        // Validate input to prevent injection attacks
        $validated = $request->validate([
            'target' => ['required', 'string', 'max:100', 'regex:/^[0-9\.\/:a-zA-Z\-]+$/'],
            'creds' => ['nullable', 'array'],
            'creds.user' => ['nullable', 'string', 'max:255'],
            'creds.pass' => ['nullable', 'string', 'max:255'],
        ], [
            'target.required' => 'Target IP or range is required.',
            'target.regex' => 'Invalid target format. Only IP addresses and CIDR ranges are allowed.',
        ]);

        $target = $validated['target'];
        $creds = $validated['creds'] ?? null;

        // Rate limiting: 10 scans per minute per user
        $key = 'mqtt_scan:' . auth()->id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Too many scan requests. Please wait before scanning again.'
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
        $apiKey = env('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME');

        // Create scan history record
        $scanHistory = MqttScanHistory::create([
            'user_id' => auth()->id(),
            'target' => $target,
            'credentials' => $creds,
            'started_at' => now(),
            'status' => 'running',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Log scan activity
            Log::info('MQTT scan initiated', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'target' => $target,
                'scan_history_id' => $scanHistory->id,
                'ip_address' => $request->ip(),
                'timestamp' => now()
            ]);

            // Add 30 second timeout for scan operations
            $response = Http::timeout(30)->withHeaders([
                'X-API-KEY' => $apiKey,
            ])->post($flaskBase . '/api/scan', [
                        'target' => $target,
                        'creds' => $creds,
                    ]);

            // Parse response and store results in database
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results']) && is_array($data['results'])) {
                    $this->storeResults($scanHistory, $data['results']);
                    $scanHistory->markCompleted();
                    $scanHistory->updateStatistics();
                }
            } else {
                $scanHistory->markFailed('Scan request failed with status: ' . $response->status());
            }

            return response($response->body(), $response->status())
                ->header('Content-Type', $response->header('Content-Type', 'application/json'));
        } catch (\Exception $e) {
            $scanHistory->markFailed($e->getMessage());

            Log::error('MQTT Scan error', [
                'user_id' => auth()->id(),
                'target' => $target,
                'scan_history_id' => $scanHistory->id,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip()
            ]);
            return response()->json(['error' => 'Failed to reach scanner: ' . $e->getMessage()], 500);
        }
    }

    // Laravel server-side endpoint: retrieve results from database or Flask fallback
    public function results()
    {
        try {
            // First, try to get results from database (latest scan)
            $latestScan = MqttScanHistory::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestScan && $latestScan->results()->count() > 0) {
                $results = $latestScan->results()->get()->map(function ($result) {
                    return [
                        'ip' => $result->ip,
                        'port' => $result->port,
                        'status' => $result->status,
                        'outcome' => $result->outcome,
                        'auth_required' => $result->auth_required,
                        'anonymous_allowed' => $result->anonymous_allowed,
                        'tls' => $result->tls,
                        'cert_subject' => $result->cert_subject,
                        'cert_issuer' => $result->cert_issuer,
                        'cert_not_before' => $result->cert_not_before,
                        'cert_not_after' => $result->cert_not_after,
                        'cert_error' => $result->cert_error,
                        'sys_topic_count' => $result->sys_topic_count,
                        'regular_topic_count' => $result->regular_topic_count,
                        'retained_count' => $result->retained_count,
                        'topics' => $result->topics,
                        'publishers' => $result->publishers,
                        'error' => $result->error,
                    ];
                });

                return response()->json([
                    'status' => 'ok',
                    'results' => $results,
                    'source' => 'database',
                    'scan_id' => $latestScan->id,
                    'scan_date' => $latestScan->completed_at ?? $latestScan->created_at,
                ]);
            }

            // Fallback: fetch from Flask API (for backward compatibility)
            $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
            $apiKey = env('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME');

            $response = Http::timeout(5)->withHeaders([
                'X-API-KEY' => $apiKey,
            ])->get($flaskBase . '/api/results');

            return response($response->body(), $response->status())
                ->header('Content-Type', $response->header('Content-Type', 'application/json'));

        } catch (\Exception $e) {
            Log::error('MQTT Results fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch results: ' . $e->getMessage()], 503);
        }
    }

    /**
     * Store scan results in database
     */
    private function storeResults(MqttScanHistory $scanHistory, array $results): void
    {
        DB::beginTransaction();
        try {
            foreach ($results as $result) {
                // Prepare certificate dates
                $certNotBefore = null;
                $certNotAfter = null;

                if (!empty($result['cert_not_before'])) {
                    try {
                        $certNotBefore = date('Y-m-d H:i:s', strtotime($result['cert_not_before']));
                    } catch (\Exception $e) {
                        Log::warning('Invalid cert_not_before date: ' . $result['cert_not_before']);
                    }
                }

                if (!empty($result['cert_not_after'])) {
                    try {
                        $certNotAfter = date('Y-m-d H:i:s', strtotime($result['cert_not_after']));
                    } catch (\Exception $e) {
                        Log::warning('Invalid cert_not_after date: ' . $result['cert_not_after']);
                    }
                }

                // Handle outcome field - can be dict or string
                $outcome = null;
                if (!empty($result['outcome'])) {
                    $outcome = is_array($result['outcome'])
                        ? json_encode($result['outcome'])
                        : $result['outcome'];
                }

                // Prepare JSON fields - handle arrays and already-encoded strings
                $certSubject = null;
                if (!empty($result['cert_subject'])) {
                    $certSubject = is_array($result['cert_subject'])
                        ? json_encode($result['cert_subject'])
                        : $result['cert_subject'];
                }

                $certIssuer = null;
                if (!empty($result['cert_issuer'])) {
                    $certIssuer = is_array($result['cert_issuer'])
                        ? json_encode($result['cert_issuer'])
                        : $result['cert_issuer'];
                }

                $topics = json_encode(!empty($result['topics']) ? $result['topics'] : []);
                $publishers = json_encode(!empty($result['publishers']) ? $result['publishers'] : []);

                // Map Flask response fields to database schema
                // Flask uses 'result' for status and 'classification'
                $status = $result['result'] ?? $result['status'] ?? $result['classification'] ?? 'unknown';

                MqttScanResult::create([
                    'user_id' => $scanHistory->user_id,
                    'scan_history_id' => $scanHistory->id,
                    'ip' => $result['ip'] ?? null,
                    'port' => $result['port'] ?? 1883,
                    'status' => $status,
                    'outcome' => $outcome,
                    'auth_required' => $result['auth_required'] ?? 'unknown',
                    'anonymous_allowed' => ($result['auth_required'] ?? '') === 'no',
                    'tls' => $result['tls'] ?? false,
                    'cert_subject' => $certSubject,
                    'cert_issuer' => $certIssuer,
                    'cert_not_before' => $certNotBefore,
                    'cert_not_after' => $certNotAfter,
                    'cert_error' => $result['cert_error'] ?? null,
                    'sys_topic_count' => $result['sys_topic_count'] ?? 0,
                    'regular_topic_count' => $result['regular_topic_count'] ?? 0,
                    'retained_count' => $result['retained_count'] ?? 0,
                    'topics' => $topics,
                    'publishers' => $publishers,
                    'error' => $result['error'] ?? null,
                ]);
            }
            DB::commit();
            Log::info('Stored ' . count($results) . ' scan results in database for scan history ID: ' . $scanHistory->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store scan results: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get scan history for the authenticated user
     */
    public function scanHistory()
    {
        try {
            $scans = MqttScanHistory::where('user_id', auth()->id())
                ->withCount('results')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($scan) {
                    return [
                        'id' => $scan->id,
                        'target' => $scan->target,
                        'started_at' => $scan->started_at,
                        'completed_at' => $scan->completed_at,
                        'duration' => $scan->duration,
                        'status' => $scan->status,
                        'total_targets' => $scan->total_targets,
                        'reachable_count' => $scan->reachable_count,
                        'unreachable_count' => $scan->unreachable_count,
                        'vulnerable_count' => $scan->vulnerable_count,
                        'result_count' => $scan->results_count,
                        'error_message' => $scan->error_message,
                    ];
                });

            return response()->json([
                'status' => 'ok',
                'scans' => $scans,
            ]);
        } catch (\Exception $e) {
            Log::error('Scan history fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch scan history'], 500);
        }
    }

    /**
     * Get results for a specific scan
     */
    public function scanHistoryResults($scanId)
    {
        try {
            $scan = MqttScanHistory::where('user_id', auth()->id())
                ->where('id', $scanId)
                ->firstOrFail();

            // Get results and let Laravel handle JSON casting automatically
            $results = $scan->results;

            // Add additional fields for compatibility
            $results = $results->map(function ($result) {
                $data = $result->toArray();
                // Add compatibility aliases for status field
                $data['result'] = $result->status;
                $data['classification'] = $result->status;
                return $data;
            });

            return response()->json([
                'status' => 'ok',
                'results' => $results,
                'scan' => [
                    'id' => $scan->id,
                    'target' => $scan->target,
                    'started_at' => $scan->started_at,
                    'duration' => $scan->duration,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Scan results fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch scan results'], 404);
        }
    }
}
