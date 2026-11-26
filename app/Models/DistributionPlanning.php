<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    /**
     * Colonne historique (distributeur unique). Conservee pour compatibilite :
     * la source de verite des droits d'acces est la table des affectations.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(DistributionPlanningAgent::class, 'planning_id');
    }

    /** Lignes d'affectation des distributeurs uniquement. */
    public function distributorAgents(): HasMany
    {
        return $this->agents()->where('type', 'distributor');
    }

    /** Lignes d'affectation des scanners uniquement. */
    public function scannerAgents(): HasMany
    {
        return $this->agents()->where('type', 'scanner');
    }

    public function distributors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'distribution_planning_agents', 'planning_id', 'user_id')
            ->wherePivot('type', DistributionPlanningAgent::TYPE_DISTRIBUTOR)
            ->withPivot('type', 'assigned_by')->withTimestamps();
    }

    public function scanners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'distribution_planning_agents', 'planning_id', 'user_id')
            ->wherePivot('type', DistributionPlanningAgent::TYPE_SCANNER)
            ->withPivot('type', 'assigned_by')->withTimestamps();
    }

    /** Plannings sur lesquels l'utilisateur a l'affectation $type (distributor | scanner). */
    public function scopeAssignedTo(Builder $query, int $userId, string $type): Builder
    {
        return $query->whereHas('agents', fn ($a) => $a->where('user_id', $userId)->where('type', $type));
    }

    public function hasAgent(int $userId, string $type): bool
    {
        return $this->agents()->where('user_id', $userId)->where('type', $type)->exists();
    }

    /**
     * Remplace l'ensemble des agents d'un type donne (les deux types sont independants :
     * synchroniser les scanners ne touche jamais aux distributeurs, et inversement).
     * Pour les distributeurs, la colonne historique assigned_to reste alignee sur le premier.
     */
    public function syncAgents(string $type, array $userIds, ?int $assignedBy = null): void
    {
        $wanted = collect($userIds)->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)->unique()->values();

        DB::transaction(function () use ($type, $wanted, $assignedBy) {
            $this->agents()->where('type', $type)->whereNotIn('user_id', $wanted)->delete();

            $existing = $this->agents()->where('type', $type)->pluck('user_id');
            foreach ($wanted->diff($existing) as $userId) {
                $this->agents()->create(['user_id' => $userId, 'type' => $type, 'assigned_by' => $assignedBy]);
            }

            if ($type === DistributionPlanningAgent::TYPE_DISTRIBUTOR) {
                $first = $this->agents()->where('type', $type)->orderBy('id')->value('user_id');
                static::whereKey($this->id)->update(['assigned_to' => $first]);
                $this->assigned_to = $first;
            }
        });
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
