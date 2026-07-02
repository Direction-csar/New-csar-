<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'category',
        'body',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'status',
        'published_at',
        'scheduled_at',
        'created_by',
        'updated_by',
        'order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
            if ($content->status === 'published' && empty($content->published_at)) {
                $content->published_at = now();
            }
        });

        static::updating(function ($content) {
            if ($content->isDirty('status') && $content->status === 'published' && empty($content->published_at)) {
                $content->published_at = now();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '>', now());
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'published' => '<span class="badge bg-success">Publié</span>',
            'draft' => '<span class="badge bg-secondary">Brouillon</span>',
            'scheduled' => '<span class="badge bg-info">Programmé</span>',
            default => '<span class="badge bg-light text-dark">Inconnu</span>',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'page' => 'Page',
            'article' => 'Article',
            'announcement' => 'Annonce',
            'banner' => 'Bannière',
            'footer' => 'Pied de page',
            default => $this->type,
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'general' => 'Général',
            'news' => 'Actualité',
            'announcements' => 'Annonces',
            'about' => 'À propos',
            'home' => 'Accueil',
            default => $this->category,
        };
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
