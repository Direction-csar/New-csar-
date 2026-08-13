<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    use HasFactory;

    protected $table = 'livraisons';

    protected $fillable = [
        'bon_matiere_id', 'transporter', 'phone', 'delivery_date',
        'gps_coordinates', 'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function bonMatiere()
    {
        return $this->belongsTo(BonMatiere::class);
    }
}
