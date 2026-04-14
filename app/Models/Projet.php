<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'statut',
        'icon',
        'image',
        'date_debut',
        'date_fin',
        'region',
        'budget',
        'lien_sim',
        'is_published',
        'position',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'lien_sim' => 'boolean',
        'is_published' => 'boolean',
        'position' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'actif' => 'En cours',
            'termine' => 'Terminé',
            'suspendu' => 'Suspendu',
        ];
        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'actif' => 'success',
            'termine' => 'secondary',
            'suspendu' => 'warning',
        ];
        return $badges[$this->statut] ?? 'secondary';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
