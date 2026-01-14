<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scan = App\Models\MqttScanHistory::find(9);

if (!$scan) {
    echo "Scan #9 not found\n";
    exit;
}

echo "=== SCAN #9 INFO ===\n";
echo "Target: {$scan->target}\n";
echo "Status: {$scan->status}\n";
echo "Reachable Count: {$scan->reachable_count}\n";
echo "Vulnerable Count: {$scan->vulnerable_count}\n";
echo "Total Targets: {$scan->total_targets}\n";
echo "\n";

$results = $scan->results;
echo "=== RESULTS ({$results->count()} records) ===\n";

foreach ($results as $result) {
    echo "\n{$result->ip}:{$result->port}\n";
    echo "  Status: {$result->status}\n";
    echo "  Auth Required: {$result->auth_required}\n";
    echo "  TLS: " . ($result->tls ? 'Yes' : 'No') . "\n";

    // Check publishers field
    echo "  Publishers (raw): " . var_export($result->getAttributes()['publishers'], true) . "\n";
    echo "  Publishers (cast): " . var_export($result->publishers, true) . "\n";

    // Check outcome field
    echo "  Outcome (raw): " . var_export($result->getAttributes()['outcome'], true) . "\n";
    echo "  Outcome (cast): " . var_export($result->outcome, true) . "\n";
}
