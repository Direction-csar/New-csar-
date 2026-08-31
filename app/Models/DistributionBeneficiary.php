<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionBeneficiary extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_TICKET_ISSUED = 'ticket_issued';
    public const STATUS_KIT_COLLECTED = 'kit_collected';

    protected $fillable = [
        'planning_id', 'full_name', 'phone', 'cni', 'address',
        'category', 'quantity_kg',
        'is_vulnerable', 'is_pregnant', 'is_elderly', 'is_disabled',
        'status', 'validated_at', 'validated_by',
    ];

    protected $casts = [
        'quantity_kg' => 'decimal:2',
        'is_vulnerable' => 'boolean',
        'is_pregnant' => 'boolean',
        'is_elderly' => 'boolean',
        'is_disabled' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function planning(): BelongsTo
    {
        return $this->belongsTo(DistributionPlanning::class, 'planning_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(DistributionTicket::class, 'beneficiary_id');
    }

    public function latestTicket()
    {
        return $this->tickets()->latest()->first();
    }
}
