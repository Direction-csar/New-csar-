<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimCollectionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_collection_id',
        'user_id',
        'status',
        'label',
        'latitude',
        'longitude',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'metadata' => 'array',
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
