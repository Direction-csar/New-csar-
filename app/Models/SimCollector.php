<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SimCollector extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password_hash',
        'assigned_zones',
        'status',
        'last_sync',
        'total_collections'
    ];

    protected $casts = [
        'assigned_zones' => 'array',
        'last_sync' => 'datetime',
        'total_collections' => 'integer'
    ];

    public function mobileCollections(): HasMany
    {
        return $this->hasMany(SimMobileCollection::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SimSyncLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canAccessZone(string $zone): bool
    {
        return in_array($zone, $this->assigned_zones ?? []);
    }

    public function incrementCollectionCount(): void
    {
        $this->increment('total_collections');
        $this->update(['last_sync' => now()]);
    }
}
