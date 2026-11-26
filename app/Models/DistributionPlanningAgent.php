<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionPlanningAgent extends Model
{
    public const TYPE_DISTRIBUTOR = 'distributor';
    public const TYPE_SCANNER = 'scanner';
    public const TYPES = [self::TYPE_DISTRIBUTOR, self::TYPE_SCANNER];

    protected $fillable = ['planning_id', 'user_id', 'type', 'assigned_by'];

    public function planning(): BelongsTo
    {
        return $this->belongsTo(DistributionPlanning::class, 'planning_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
