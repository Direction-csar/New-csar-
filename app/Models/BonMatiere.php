<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonMatiere extends Model
{
    use HasFactory;

    protected $table = 'bon_matieres';

    protected $fillable = [
        'planning_id', 'beneficiaire_id', 'numero_bon', 'quantite_kg', 'categorie',
        'statut', 'attributed_at', 'delivered_at', 'cancelled_at',
        'attributed_by', 'delivered_by',
    ];

    protected $casts = [
        'quantite_kg' => 'float',
        'attributed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    public function beneficiaire()
    {
        return $this->belongsTo(Beneficiaire::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    public function livraison()
    {
        return $this->hasOne(Livraison::class);
    }

    public function attributedBy()
    {
        return $this->belongsTo(User::class, 'attributed_by');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}
