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
        $target = $request->input('target', '127.0.0.1');
        $creds = $request->input('creds', null); // optional array {user, pass}

        $flaskBase = env('FLASK_BASE', 'http://127.0.0.1:5000');
        $apiKey = env('FLASK_API_KEY', 'my-very-secret-flask-key-CHANGEME');

        try {
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
            Log::error('MQTT Scan error: ' . $e->getMessage());
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
