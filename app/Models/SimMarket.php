<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimMarket extends Model
{
    use HasFactory;

    public const TYPE_RURAL_COLLECTE = 'rural_collecte';
    public const TYPE_RURAL_CONSOMMATION = 'rural_consommation';
    public const TYPE_URBAIN = 'urbain';
    public const TYPE_FRONTALIER = 'frontalier';
    public const TYPE_REGROUPEMENT = 'regroupement';
    public const TYPE_TRANSFRONTALIER = 'transfrontalier';

    protected $fillable = [
        'sim_department_id',
        'created_by',
        'name',
        'slug',
        'commune',
        'market_type',
        'market_day',
        'is_permanent',
        'latitude',
        'longitude',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function department()
    {
        return $this->belongsTo(SimDepartment::class, 'sim_department_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(SimCollectorAssignment::class, 'sim_market_id');
    }

    public function collections()
    {
        return $this->hasMany(SimCollection::class, 'sim_market_id');
    }
}
