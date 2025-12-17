<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Laravel HTTP client
use Illuminate\Support\Facades\Log;

class MqttScannerController extends Controller
{
    // Show dashboard (loads without blocking on Flask API)
    public function index()
    {
        // Don't fetch results server-side to avoid timeout
        // Let the frontend fetch results asynchronously via AJAX
        // Pass empty array for backward compatibility with the view
        $scans = [];
        return view('dashboard', compact('scans'));
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

        try {
            // Log scan activity
            Log::info('MQTT scan initiated', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'target' => $target,
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

            return response($response->body(), $response->status())
                ->header('Content-Type', $response->header('Content-Type', 'application/json'));
        } catch (\Exception $e) {
            Log::error('MQTT Scan error', [
                'user_id' => auth()->id(),
                'target' => $target,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip()
            ]);
            return response()->json(['error' => 'Failed to reach scanner: ' . $e->getMessage()], 500);
        }
    }

    // Laravel server-side endpoint: calls Flask /api/results
    public function results()
    {
        $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
        $apiKey = env('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME');

        try {
            // Add 5 second timeout for results fetch
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
}
