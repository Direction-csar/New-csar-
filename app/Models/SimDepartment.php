<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_region_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function region()
    {
        return $this->belongsTo(SimRegion::class, 'sim_region_id');
    }

    public function markets()
    {
        return $this->hasMany(SimMarket::class);
    }
}
