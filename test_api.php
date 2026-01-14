<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate authenticated user
$user = App\Models\User::first();
auth()->login($user);

// Test the scanHistoryResults method
$controller = new App\Http\Controllers\MqttScannerController();
$response = $controller->scanHistoryResults(9);

echo "=== API Response for Scan #9 ===\n";
$data = json_decode($response->getContent(), true);

if (isset($data['results'])) {
    echo "Total results: " . count($data['results']) . "\n\n";

    foreach ($data['results'] as $idx => $result) {
        echo "Result #" . ($idx + 1) . ":\n";
        echo "  IP:Port: {$result['ip']}:{$result['port']}\n";
        echo "  Status: {$result['status']}\n";

        // Check publishers type
        echo "  Publishers type: " . gettype($result['publishers']) . "\n";
        if (is_array($result['publishers'])) {
            echo "  Publishers count: " . count($result['publishers']) . "\n";
            echo "  First publisher topic: " . ($result['publishers'][0]['topic'] ?? 'N/A') . "\n";
        } else {
            echo "  Publishers value: " . substr($result['publishers'], 0, 100) . "...\n";
        }

        // Check outcome type
        echo "  Outcome type: " . gettype($result['outcome']) . "\n";
        if (is_array($result['outcome'])) {
            echo "  Outcome label: " . ($result['outcome']['label'] ?? 'N/A') . "\n";
        } else {
            echo "  Outcome value: " . substr($result['outcome'], 0, 100) . "...\n";
        }

        echo "\n";
    }
} else {
    echo "Error: " . ($data['error'] ?? 'Unknown error') . "\n";
}
