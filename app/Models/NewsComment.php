<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
        'ip_address',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Relation vers l'actualité
     */
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Scope pour les commentaires approuvés
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
