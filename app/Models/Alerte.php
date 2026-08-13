<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $table = 'alertes';

    protected $fillable = [
        'type', 'campaign_id', 'planning_id', 'controle', 'valeur',
        'seuil', 'status', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'valeur' => 'float',
        'seuil' => 'float',
        'resolved_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
