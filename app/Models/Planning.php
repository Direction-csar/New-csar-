<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    protected $table = 'plannings';

    protected $fillable = [
        'campaign_id', 'name', 'category', 'planned_quota_kg',
        'executed_quota_kg', 'alert_threshold_kg', 'warehouse_id', 'status',
    ];

    protected $casts = [
        'planned_quota_kg' => 'float',
        'executed_quota_kg' => 'float',
        'alert_threshold_kg' => 'float',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function beneficiaires()
    {
        return $this->hasMany(Beneficiaire::class);
    }

    public function bonMatieres()
    {
        return $this->hasMany(BonMatiere::class);
    }

    public function agents()
    {
        return $this->belongsToMany(User::class, 'planning_user');
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}
