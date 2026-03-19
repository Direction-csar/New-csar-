<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'organization',
        'type',
        'mission_location',
        'mission_date',
        'message',
        'status',
        'rating',
        'is_featured'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'integer',
        'mission_date' => 'date',
    ];

    /**
     * Scope pour récupérer uniquement les témoignages approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope pour récupérer les témoignages en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pour récupérer les témoignages en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour récupérer les témoignages de mission
     */
    public function scopeMission($query)
    {
        return $query->where('type', 'mission');
    }

    /**
     * Scope pour récupérer les témoignages généraux
     */
    public function scopeGeneral($query)
    {
        return $query->where('type', 'general');
    }
}
