<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectorLocation extends Model
{
    protected $fillable = [
        'collector_id',
        'latitude',
        'longitude',
        'accuracy',
        'status',
        'current_market',
        'collections_today',
        'last_activity_at',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'last_activity_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(SimCollector::class, 'collector_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'collecting';
    }

    public function isOnline(): bool
    {
        return $this->last_activity_at->diffInMinutes(now()) <= 5;
    }
}
