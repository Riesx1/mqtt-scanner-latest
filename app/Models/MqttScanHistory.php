<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MqttScanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target',
        'credentials',
        'started_at',
        'completed_at',
        'duration',
        'status',
        'total_targets',
        'reachable_count',
        'unreachable_count',
        'vulnerable_count',
        'ip_address',
        'user_agent',
        'error_message',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration' => 'float',
        'total_targets' => 'integer',
        'reachable_count' => 'integer',
        'unreachable_count' => 'integer',
        'vulnerable_count' => 'integer',
    ];

    /**
     * Get the user that owns the scan history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all scan results for this scan.
     */
    public function results(): HasMany
    {
        return $this->hasMany(MqttScanResult::class, 'scan_history_id');
    }

    /**
     * Mark the scan as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration' => $this->started_at->diffInSeconds(now()),
        ]);
    }

    /**
     * Mark the scan as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration' => $this->started_at->diffInSeconds(now()),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update scan statistics based on results.
     */
    public function updateStatistics(): void
    {
        $results = $this->results()->get();

        $this->update([
            'total_targets' => $results->count(),
            'reachable_count' => $results->where('status', 'reachable')->count(),
            'unreachable_count' => $results->where('status', 'unreachable')->count(),
            'vulnerable_count' => $results->where('anonymous_allowed', true)->count(),
        ]);
    }
}
