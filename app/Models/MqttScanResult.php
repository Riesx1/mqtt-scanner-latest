<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MqttScanResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scan_history_id',
        'ip',
        'port',
        'status',
        'outcome',
        'auth_required',
        'anonymous_allowed',
        'tls',
        'cert_subject',
        'cert_issuer',
        'cert_not_before',
        'cert_not_after',
        'cert_error',
        'sys_topic_count',
        'regular_topic_count',
        'retained_count',
        'topics',
        'publishers',
        'error',
        'response_time',
    ];

    protected $casts = [
        'anonymous_allowed' => 'boolean',
        'tls' => 'boolean',
        'cert_subject' => 'array',
        'cert_issuer' => 'array',
        'cert_not_before' => 'datetime',
        'cert_not_after' => 'datetime',
        'sys_topic_count' => 'integer',
        'regular_topic_count' => 'integer',
        'retained_count' => 'integer',
        'topics' => 'array',
        'publishers' => 'array',
        'outcome' => 'array',
        'response_time' => 'float',
    ];

    /**
     * Get the user that owns the scan result.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scan history this result belongs to.
     */
    public function scanHistory(): BelongsTo
    {
        return $this->belongsTo(MqttScanHistory::class, 'scan_history_id');
    }

    /**
     * Check if this result indicates a vulnerable broker (anonymous access).
     */
    public function isVulnerable(): bool
    {
        return $this->anonymous_allowed === true;
    }

    /**
     * Check if the broker is reachable.
     */
    public function isReachable(): bool
    {
        return in_array($this->status, ['open', 'reachable', 'connected']);
    }
}
