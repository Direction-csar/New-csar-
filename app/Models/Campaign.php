<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'start_date', 'end_date',
        'initial_stock_kg', 'executed_stock_kg', 'status', 'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
        'initial_stock_kg' => 'float',
        'executed_stock_kg' => 'float',
    ];

    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
}
