<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicDocument extends Model
{
    use HasFactory;

    protected $table = 'public_documents';

    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'is_published',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'date',
        'expires_at' => 'date',
    ];

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRecrutement($query)
    {
        return $query->where('type', 'recrutement');
    }

    public function scopeRapport($query)
    {
        return $query->where('type', 'rapport');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }

    // Accessors
    public function getTypeLabelAttribute()
    {
        $labels = [
            'recrutement' => 'AVIS DE RECRUTEMENT',
            'rapport' => 'RAPPORT ANNUEL',
            'communique' => 'COMMUNIQUÉ',
            'appel_offre' => 'APPEL D\'OFFRE',
            'autre' => 'DOCUMENT',
        ];

        return $labels[$this->type] ?? 'DOCUMENT';
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    public function getIconClassAttribute()
    {
        $icons = [
            'recrutement' => 'fas fa-file-pdf',
            'rapport' => 'fas fa-file-alt',
            'communique' => 'fas fa-bullhorn',
            'appel_offre' => 'fas fa-gavel',
            'autre' => 'fas fa-file',
        ];

        return $icons[$this->type] ?? 'fas fa-file';
    }

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
