<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectorWarehouseAssignment extends Model
{
    protected $fillable = [
        'inspector_id', 'warehouse_id', 'assigned_by', 'note',
        'is_active', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'revoked_at' => 'datetime',
    ];

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
