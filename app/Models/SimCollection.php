<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimCollection extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'sim_market_id',
        'collector_id',
        'supervisor_id',
        'validated_by',
        'rejected_by',
        'collected_on',
        'market_day',
        'status',
        'started_at',
        'arrived_at',
        'submitted_at',
        'reviewed_at',
        'validated_at',
        'rejected_at',
        'last_activity_at',
        'progress_percentage',
        'has_live_tracking',
        'observations',
        'admin_notes',
    ];

    protected $casts = [
        'collected_on' => 'date',
        'started_at' => 'datetime',
        'arrived_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'progress_percentage' => 'integer',
        'has_live_tracking' => 'boolean',
    ];

    public function market()
    {
        return $this->belongsTo(SimMarket::class, 'sim_market_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items()
    {
        return $this->hasMany(SimCollectionItem::class, 'sim_collection_id');
    }

    public function statuses()
    {
        return $this->hasMany(SimCollectionStatus::class, 'sim_collection_id');
    }

    public function positions()
    {
        return $this->hasMany(SimCollectionPosition::class, 'sim_collection_id');
    }
}
