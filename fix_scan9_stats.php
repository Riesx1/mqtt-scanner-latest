<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scan = App\Models\MqttScanHistory::find(9);

if (!$scan) {
    echo "Scan #9 not found\n";
    exit;
}

echo "=== Before Update ===\n";
echo "Reachable Count: {$scan->reachable_count}\n";
echo "Vulnerable Count: {$scan->vulnerable_count}\n";
echo "Total Targets: {$scan->total_targets}\n\n";

// Manually calculate statistics from results
$results = $scan->results;
$reachable = 0;
$vulnerable = 0;

foreach ($results as $result) {
    // Count as reachable if status is 'connected' or similar
    if (in_array($result->status, ['connected', 'open_or_auth_ok', 'not_authorized'])) {
        $reachable++;
    }

    // Count as vulnerable if anonymous access is allowed or no auth required
    if ($result->anonymous_allowed || $result->auth_required === 'no') {
        $vulnerable++;
    }
}

// Update the scan statistics
$scan->update([
    'reachable_count' => $reachable,
    'vulnerable_count' => $vulnerable,
    'unreachable_count' => $scan->total_targets - $reachable,
]);

echo "=== After Update ===\n";
echo "Reachable Count: {$scan->reachable_count}\n";
echo "Vulnerable Count: {$scan->vulnerable_count}\n";
echo "Unreachable Count: {$scan->unreachable_count}\n";
echo "\nStatistics updated successfully!\n";
