<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionPlanning extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'description',
        'planned_quota_kg', 'executed_kg', 'expected_beneficiaries',
        'status', 'distribution_date', 'location', 'assigned_to',
    ];

    protected $casts = [
        'planned_quota_kg' => 'decimal:2',
        'executed_kg' => 'decimal:2',
        'distribution_date' => 'date',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(DistributionEvent::class, 'event_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(DistributionBeneficiary::class, 'planning_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(DistributionTicket::class, 'planning_id');
    }

    public function getExecutionRateAttribute(): float
    {
        if ($this->planned_quota_kg <= 0) {
            return 0;
        }
        return round((float) $this->executed_kg / (float) $this->planned_quota_kg * 100, 1);
    }

    public function getInProgressKgAttribute(): float
    {
        return (float) $this->planned_quota_kg - (float) $this->executed_kg;
    }

    public function getAlertLevelAttribute(): string
    {
        $rate = $this->execution_rate;
        if ($rate >= 100) return 'ok';
        if ($rate >= 80) return 'watch';
        if ($rate > 0) return 'delay';
        return 'not_started';
    }

    public function getValidatedBeneficiariesCountAttribute(): int
    {
        return $this->beneficiaries()->whereIn('status', ['validated', 'ticket_issued', 'kit_collected'])->count();
    }

    public function getTicketsIssuedCountAttribute(): int
    {
        return $this->tickets()->whereIn('status', ['issued', 'scanned', 'collected'])->count();
    }

    public function getTicketsCollectedCountAttribute(): int
    {
        return $this->tickets()->where('status', 'collected')->count();
    }
}
