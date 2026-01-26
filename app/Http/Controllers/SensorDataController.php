<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SensorDataController extends Controller
{
    /**
     * Display real-time sensor dashboard
     */
    public function index()
    {
        // Get latest sensor reading
        $latestReading = SensorReading::orderBy('created_at', 'desc')->first();

        // Get readings from last 24 hours for charts
        $readings24h = SensorReading::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'asc')
            ->get();

        // Get summary statistics
        $stats = [
            'total_readings' => SensorReading::count(),
            'avg_temperature' => SensorReading::avg('temperature'),
            'avg_humidity' => SensorReading::avg('humidity'),
            'avg_light' => SensorReading::avg('ldr_pct'),
            'pir_detections' => SensorReading::where('pir', true)->count(),
        ];

        return view('sensors.dashboard', compact('latestReading', 'readings24h', 'stats'));
    }

    /**
     * Get latest sensor data as JSON for AJAX polling
     */
    public function getLatest()
    {
        $reading = SensorReading::orderBy('created_at', 'desc')->first();

        if (!$reading) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No sensor readings available yet'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'temperature' => $reading->temperature,
                'humidity' => $reading->humidity,
                'ldr_raw' => $reading->ldr_raw,
                'ldr_pct' => $reading->ldr_pct,
                'pir' => $reading->pir,
                'device' => $reading->device,
                'timestamp' => $reading->created_at->toIso8601String(),
                'formatted_time' => $reading->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get historical data for charts (last N hours)
     */
    public function getHistory(Request $request)
    {
        $hours = $request->input('hours', 24);

        $readings = SensorReading::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get();

        $chartData = [
            'labels' => [],
            'temperature' => [],
            'humidity' => [],
            'light' => [],
        ];

        foreach ($readings as $reading) {
            $chartData['labels'][] = $reading->created_at->format('H:i');
            $chartData['temperature'][] = $reading->temperature;
            $chartData['humidity'][] = $reading->humidity;
            $chartData['light'][] = $reading->ldr_pct;
        }

        return response()->json([
            'status' => 'ok',
            'data' => $chartData
        ]);
    }

    /**
     * Get connection status (check if new data is coming in)
     */
    public function getStatus()
    {
        $latestReading = SensorReading::orderBy('created_at', 'desc')->first();

        if (!$latestReading) {
            return response()->json([
                'connected' => false,
                'message' => 'No data received yet',
                'last_update' => null
            ]);
        }

        // Consider connected if last reading was within 5 minutes
        $isConnected = $latestReading->created_at->diffInMinutes(now()) < 5;

        return response()->json([
            'connected' => $isConnected,
            'message' => $isConnected ? 'Receiving data' : 'No recent data',
            'last_update' => $latestReading->created_at->toIso8601String(),
            'time_ago' => $latestReading->created_at->diffForHumans()
        ]);
    }

    /**
     * Get detailed security analysis of MQTT broker
     */
    public function getSecurityAnalysis()
    {
        try {
            // Fetch scan results from Flask backend with API key authentication
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-KEY' => 'my-very-secret-flask-key-CHANGEME'
                ])
                ->get('http://127.0.0.1:5000/api/results');

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Flask scanner backend is not running. Please start the Flask server on port 5000.',
                    'help' => 'Run: python mqtt-scanner/app.py'
                ]);
            }

            $scanResults = $response->json() ?? [];

            // Find secure (8883) and insecure (1883) broker scans
            $secureBroker = collect($scanResults)->first(fn($scan) => $scan['port'] == 8883);
            $insecureBroker = collect($scanResults)->first(fn($scan) => $scan['port'] == 1883);

            // Get sensor reading stats
            $latestReading = SensorReading::latest()->first();
            $totalReadings = SensorReading::count();

            return response()->json([
                'status' => 'ok',
                'data' => [
                    'secure_broker' => $this->formatBrokerDetails($secureBroker, 'secure'),
                    'insecure_broker' => $this->formatBrokerDetails($insecureBroker, 'insecure'),
                    'sensor_stats' => [
                        'total_readings' => $totalReadings,
                        'latest_reading' => $latestReading,
                        'active_topic' => $latestReading->topic ?? 'N/A'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not connect to Flask backend: ' . $e->getMessage(),
                'help' => 'Make sure Flask scanner is running on http://127.0.0.1:5000'
            ]);
        }
    }

    /**
     * Format broker details for security report
     */
    private function formatBrokerDetails($broker, $type)
    {
        if (!$broker) {
            return [
                'available' => false,
                'port' => $type === 'secure' ? 8883 : 1883,
                'message' => 'Broker not scanned or not available'
            ];
        }

        $publishers = $broker['publishers'] ?? [];
        $subscribers = $broker['subscribers'] ?? [];
        $hasTls = $broker['tls'] ?? false;

        // Determine risk level
        $riskLevel = $this->calculateRiskLevel($broker);

        // Build certificate info from available fields
        $certInfo = 'Not a TLS port';
        if ($hasTls) {
            $certParts = [];
            if (!empty($broker['cert_subject'])) {
                $certParts[] = "Subject: " . $broker['cert_subject'];
            }
            if (!empty($broker['cert_issuer'])) {
                $certParts[] = "Issuer: " . $broker['cert_issuer'];
            }
            if (!empty($broker['cert_valid_from'])) {
                $certParts[] = "Valid From: " . $broker['cert_valid_from'];
            }
            if (!empty($broker['cert_valid_to'])) {
                $certParts[] = "Valid To: " . $broker['cert_valid_to'];
            }
            $certInfo = !empty($certParts) ? implode("\n", $certParts) : 'Certificate details not available';
        }

        return [
            'available' => true,
            'ip' => $broker['ip'] ?? '127.0.0.1',
            'port' => $broker['port'],
            'result' => $broker['result'] ?? 'unknown',
            'classification' => $broker['classification'] ?? 'unknown',
            'timestamp' => $broker['timestamp'] ?? now(),
            'tls_enabled' => $hasTls,
            'cert_info' => $certInfo,
            'risk_level' => $riskLevel,
            'publishers' => $publishers,
            'subscribers' => $subscribers,
            'publisher_count' => count($publishers),
            'subscriber_count' => count($subscribers),
            'topics' => array_unique(array_column($publishers, 'topic')),
            'topic_count' => count(array_unique(array_column($publishers, 'topic'))),
            'security_issues' => $this->identifySecurityIssues($broker),
            'recommendations' => $this->generateRecommendations($broker, $type)
        ];
    }

    /**
     * Calculate risk level based on broker configuration
     */
    private function calculateRiskLevel($broker)
    {
        $hasTls = $broker['tls'] ?? false;
        $classification = $broker['classification'] ?? '';
        $port = $broker['port'] ?? 0;

        if (!$hasTls && $port == 1883) {
            return 'CRITICAL'; // No encryption
        }

        if ($classification === 'open_or_auth_ok' && !$hasTls) {
            return 'HIGH'; // Open access without encryption
        }

        if ($hasTls && $classification === 'open_or_auth_ok') {
            return 'MEDIUM'; // Encrypted but possibly open access
        }

        if ($hasTls && $classification === 'not_authorized') {
            return 'LOW'; // Encrypted with auth required
        }

        return 'UNKNOWN';
    }

    /**
     * Identify security issues
     */
    private function identifySecurityIssues($broker)
    {
        $issues = [];
        $port = $broker['port'] ?? 0;
        $hasTls = $broker['tls'] ?? false;
        $classification = $broker['classification'] ?? '';
        $publishers = $broker['publishers'] ?? [];

        if ($port == 1883 && !$hasTls) {
            $issues[] = 'Using insecure port (1883) - no encryption';
        }

        if ($classification === 'open_or_auth_ok') {
            $issues[] = 'Anonymous access is allowed';
        }

        if (count($publishers) > 0 && !$hasTls) {
            $issues[] = count($publishers) . ' publishers detected on unsecured broker';
        }

        $topics = array_unique(array_column($publishers, 'topic'));
        if (count($topics) > 0) {
            $issues[] = count($topics) . ' active topics detected';
        }

        return $issues;
    }

    /**
     * Generate security recommendations
     */
    private function generateRecommendations($broker, $type)
    {
        $recommendations = [];
        $hasTls = $broker['tls'] ?? false;
        $classification = $broker['classification'] ?? '';

        if (!$hasTls) {
            $recommendations[] = 'Migrate to port 8883 with TLS/SSL';
        }

        if ($classification === 'open_or_auth_ok') {
            $recommendations[] = 'Enable authentication and disable anonymous access';
        }

        $recommendations[] = 'Review topic ACLs and implement proper authorization';

        if ($type === 'secure' && $hasTls) {
            $recommendations[] = 'Verify certificate validity and renewal dates';
        }

        return $recommendations;
    }

    /**
     * Get comprehensive security analysis with $SYS topics data
     */
    public function securityAnalysis()
    {
        try {
            // Call Flask API to get comprehensive scan results with broker_info
            $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
            $apiKey = env('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME');

            $response = Http::timeout(30)->withHeaders([
                'X-API-KEY' => $apiKey,
            ])->post($flaskBase . '/api/scan', [
                        'target' => '127.0.0.1',
                        'listen_duration' => 3,
                        'capture_all_topics' => false, // Only $SYS topics
                    ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Flask scanner backend not available',
                    'help' => true
                ], 500);
            }

            $data = $response->json();
            $results = $data['results'] ?? [];

            // Format results for security modal
            $secureBroker = null;
            $insecureBroker = null;

            foreach ($results as $result) {
                $port = $result['port'] ?? 0;
                $brokerData = $this->formatBrokerForSecurityModal($result);

                if ($port == 8883) {
                    $secureBroker = $brokerData;
                } elseif ($port == 1883) {
                    $insecureBroker = $brokerData;
                }
            }

            return response()->json([
                'status' => 'ok',
                'data' => [
                    'secure_broker' => $secureBroker,
                    'insecure_broker' => $insecureBroker
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to Flask scanner: ' . $e->getMessage(),
                'help' => true
            ], 500);
        }
    }

    /**
     * Format broker result for security modal with $SYS topics data
     */
    private function formatBrokerForSecurityModal($result)
    {
        $port = $result['port'] ?? 0;
        $hasTls = ($port == 8883) || ($result['tls'] ?? false);
        $classification = $result['classification'] ?? 'unknown';
        $brokerInfo = $result['broker_info'] ?? [];
        $certInfo = $result['cert_info'] ?? [];
        $publishers = $result['publishers'] ?? [];

        // Extract $SYS topics data from broker_info
        $sysTopicCount = $brokerInfo['sys_count'] ?? 0;
        $regularTopicCount = $brokerInfo['regular_count'] ?? 0;
        $retainedCount = count($brokerInfo['retained_topics'] ?? []);
        $brokerError = $brokerInfo['error'] ?? null;

        // Extract topics list
        $sysTopics = $brokerInfo['sys_topics'] ?? [];
        $regularTopics = $brokerInfo['regular_topics'] ?? [];
        $allTopics = array_merge(array_keys($sysTopics), array_keys($regularTopics));

        return [
            'ip' => $result['ip'] ?? '127.0.0.1',
            'port' => $port,
            'tls_enabled' => $hasTls,
            'result' => $result['result'] ?? 'connected',
            'classification' => $classification,
            'risk_level' => $this->calculateRiskLevel($port, $hasTls, $classification),
            'timestamp' => $result['timestamp'] ?? now()->toIso8601String(),

            // $SYS Topics & Broker Information
            'sys_topic_count' => $sysTopicCount,
            'regular_topic_count' => $regularTopicCount,
            'retained_count' => $retainedCount,
            'broker_error' => $brokerError,

            // Topics
            'topics' => $allTopics,
            'topic_count' => count($allTopics),

            // Publishers data
            'publishers' => array_map(function ($pub) {
                return [
                    'topic' => $pub['topic'] ?? 'Unknown',
                    'payload_size' => isset($pub['payload']) ? strlen($pub['payload']) : 0,
                    'qos' => $pub['qos'] ?? 0,
                    'retained' => $pub['retained'] ?? false,
                    'note' => $pub['note'] ?? null
                ];
            }, $publishers),
            'publisher_count' => count($publishers),

            // Subscribers (if available)
            'subscribers' => [],
            'subscriber_count' => 0,

            // Certificate info
            'cert_info' => $certInfo,

            // Security assessment
            'security_issues' => $this->identifySecurityIssues([
                'port' => $port,
                'tls' => $hasTls,
                'classification' => $classification,
                'publishers' => $publishers
            ]),
            'recommendations' => $this->generateRecommendations([
                'tls' => $hasTls,
                'classification' => $classification
            ], $hasTls ? 'secure' : 'insecure')
        ];
    }
}
