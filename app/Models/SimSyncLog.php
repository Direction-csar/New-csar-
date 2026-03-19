<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimSyncLog extends Model
{
    protected $fillable = [
        'collector_id',
        'data_count',
        'sync_type',
        'status',
        'error_message',
        'synced_data_ids',
        'sync_started_at',
        'sync_completed_at'
    ];

    protected $casts = [
        'data_count' => 'integer',
        'synced_data_ids' => 'array',
        'sync_started_at' => 'datetime',
        'sync_completed_at' => 'datetime'
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(SimCollector::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->sync_completed_at) {
            return null;
        }
        
        return $this->sync_started_at->diffInSeconds($this->sync_completed_at);
    }

    public function markAsCompleted(string $status = 'success', ?string $errorMessage = null): void
    {
        $this->update([
            'status' => $status,
            'error_message' => $errorMessage,
            'sync_completed_at' => now()
        ]);
    }
}
