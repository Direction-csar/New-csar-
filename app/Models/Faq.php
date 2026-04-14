<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'locale',
        'is_published',
        'position',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'position' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'usager' => 'Usager',
            'bailleur' => 'Bailleur de fonds',
            'general' => 'Général',
        ];
        return $labels[$this->category] ?? $this->category;
    }
}
