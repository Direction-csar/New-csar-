<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SimMarket;
use App\Models\SimProduct;

class SimMobileCollection extends Model
{
    protected $fillable = [
        'collector_id',
        'market_id',
        'product_id',
        'price',
        'retail_price',
        'provenance',
        'quantity_collected',
        'wholesale_price',
        'collection_date',
        'latitude',
        'longitude',
        'photos',
        'metadata',
        'sync_status',
        'synced_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'collection_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'photos' => 'array',
        'metadata' => 'json',
        'synced_at' => 'datetime'
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(SimCollector::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(SimMarket::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SimProduct::class);
    }

    public function markAsSynced(): void
    {
        $this->update([
            'sync_status' => 'synced',
            'synced_at' => now()
        ]);
    }

    public function isPending(): bool
    {
        return $this->sync_status === 'pending';
    }

    public function hasValidCoordinates(): bool
    {
        return $this->latitude && $this->longitude;
    }

    public function getMainPhotoAttribute(): ?string
    {
        return $this->photos[0] ?? null;
    }
}
