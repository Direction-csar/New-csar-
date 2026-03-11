<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimCollectionPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_collection_id',
        'user_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'speed_kmh',
        'battery_level',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy_meters' => 'decimal:2',
        'speed_kmh' => 'decimal:2',
        'battery_level' => 'integer',
        'captured_at' => 'datetime',
    ];

    public function collection()
    {
        return $this->belongsTo(SimCollection::class, 'sim_collection_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
