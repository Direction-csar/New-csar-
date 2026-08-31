<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'location',
        'initial_stock_kg', 'start_date', 'end_date',
        'status', 'created_by',
    ];

    protected $casts = [
        'initial_stock_kg' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function plannings(): HasMany
    {
        return $this->hasMany(DistributionPlanning::class, 'event_id');
    }

    public function getTotalPlannedKgAttribute(): float
    {
        return (float) $this->plannings()->sum('planned_quota_kg');
    }

    public function getTotalExecutedKgAttribute(): float
    {
        return (float) $this->plannings()->sum('executed_kg');
    }

    public function getRemainingStockKgAttribute(): float
    {
        return (float) $this->initial_stock_kg - $this->total_executed_kg;
    }

    public function getStockStatusAttribute(): string
    {
        $remaining = $this->remaining_stock_kg;
        $planned = $this->total_planned_kg;

        if ($remaining < 0) {
            return 'critical';
        }
        if ($planned > $this->initial_stock_kg) {
            return 'warning';
        }
        return 'ok';
    }

    public function getTotalBeneficiariesAttribute(): int
    {
        return DistributionBeneficiary::whereIn('planning_id', $this->plannings()->pluck('id'))->count();
    }

    public function getTotalTicketsIssuedAttribute(): int
    {
        return DistributionTicket::whereIn('planning_id', $this->plannings()->pluck('id'))->count();
    }

    public function getTotalTicketsCollectedAttribute(): int
    {
        return DistributionTicket::whereIn('planning_id', $this->plannings()->pluck('id'))
            ->where('status', 'collected')->count();
    }
}
