<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'status',
        'featured_image',
        'cover_image',
        'youtube_url',
        'document_file',
        'document_cover_image',
        'document_title',
        'is_published',
        'is_featured',
        'is_public',
        'cover_choice',
        'published_at',
        'scheduled_at',
        'created_by',
        'updated_by',
        'meta_title',
        'meta_description',
        'tags',
        'views_count',
        'downloads_count'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'tags' => 'array',
        'views_count' => 'integer',
        'downloads_count' => 'integer'
    ];

    /**
     * Obtenir l'auteur de l'actualité
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtenir les commentaires de l'actualité
     */
    public function comments()
    {
        return $this->hasMany(NewsComment::class)->approved()->latest();
    }

    /**
     * Obtenir tous les commentaires (y compris non approuvés) - pour admin
     */
    public function allComments()
    {
        return $this->hasMany(NewsComment::class)->latest();
    }

    /**
     * Obtenir l'utilisateur qui a modifié l'actualité
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope pour les actualités publiées
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope pour les actualités en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pour les actualités récentes
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }

    /**
     * Obtenir l'URL de l'image mise en avant
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }
        return asset('images/default-news.jpg');
    }

    /**
     * Obtenir l'URL de l'image de couverture
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return $this->featured_image_url;
    }

    /**
     * Obtenir l'URL de téléchargement du document
     */
    public function getDocumentUrlAttribute()
    {
        if ($this->document_file) {
            return asset('storage/' . $this->document_file);
        }
        return null;
    }

    /**
     * Obtenir le nom du fichier document
     */
    public function getDocumentNameAttribute()
    {
        if ($this->document_file) {
            return basename($this->document_file);
        }
        return $this->document_title ?? 'Document';
    }

    /**
     * Obtenir l'extension du document
     */
    public function getDocumentExtensionAttribute()
    {
        if ($this->document_file) {
            return strtolower(pathinfo($this->document_file, PATHINFO_EXTENSION));
        }
        return null;
    }

    /**
     * Vérifier si l'actualité a une vidéo
     */
    public function hasVideo()
    {
        return !empty($this->youtube_url);
    }

    /**
     * Vérifier si l'actualité a un document
     */
    public function hasDocument()
    {
        return !empty($this->document_file);
    }

    /**
     * Vérifier si l'actualité a une image
     */
    public function hasImage()
    {
        return !empty($this->featured_image) || !empty($this->cover_image);
    }

    /**
     * Obtenir la couverture principale selon le choix de l'utilisateur
     */
    public function getMainCoverAttribute()
    {
        $choice = $this->cover_choice ?? 'auto';
        
        switch ($choice) {
            case 'video':
                if ($this->hasVideo()) {
                    return [
                        'type' => 'video',
                        'url' => $this->youtube_url,
                        'thumbnail' => $this->getVideoThumbnail()
                    ];
                }
                // Fallback vers l'image si pas de vidéo
                return $this->getMainImageCover();
                
            case 'image':
                return $this->getMainImageCover();
                
            case 'auto':
            default:
                // Logique automatique : vidéo en priorité, sinon image
                if ($this->hasVideo()) {
                    return [
                        'type' => 'video',
                        'url' => $this->youtube_url,
                        'thumbnail' => $this->getVideoThumbnail()
                    ];
                }
                return $this->getMainImageCover();
        }
    }
    
    /**
     * Obtenir la couverture image principale
     */
    private function getMainImageCover()
    {
        if ($this->cover_image) {
            return [
                'type' => 'image',
                'url' => $this->cover_image_url,
                'alt' => $this->title
            ];
        } elseif ($this->featured_image) {
            return [
                'type' => 'image',
                'url' => $this->featured_image_url,
                'alt' => $this->title
            ];
        }
        return null;
    }

    /**
     * Obtenir l'image principale (vidéo > image de couverture > image mise en avant)
     * @deprecated Utiliser getMainCoverAttribute() à la place
     */
    public function getMainMediaAttribute()
    {
        return $this->getMainCoverAttribute();
    }

    /**
     * Obtenir la miniature de la vidéo YouTube
     */
    public function getVideoThumbnail()
    {
        if (!$this->youtube_url) return null;
        
        // Extraire l'ID de la vidéo YouTube
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $this->youtube_url, $matches);
        if (isset($matches[1])) {
            return "https://img.youtube.com/vi/{$matches[1]}/maxresdefault.jpg";
        }
        return null;
    }

    /**
     * Obtenir l'image de couverture du document
     */
    public function getDocumentCoverImage()
    {
        if (!$this->document_file) return null;
        
        // Si une image de couverture personnalisée est définie, l'utiliser
        if ($this->document_cover_image) {
            return asset('storage/' . $this->document_cover_image);
        }
        
        $extension = strtolower(pathinfo($this->document_file, PATHINFO_EXTENSION));
        
        // Pour les PDF, on peut générer une image de couverture par défaut
        if ($extension === 'pdf') {
            return $this->generateDocumentCoverImage();
        }
        
        // Pour d'autres types de documents, retourner une icône
        return $this->getDocumentIcon();
    }

    /**
     * Générer une image de couverture pour le document
     */
    public function generateDocumentCoverImage()
    {
        // Retourner l'image de couverture par défaut pour les PDF
        return asset('images/document-covers/pdf-default.svg');
    }

    /**
     * Obtenir l'icône du document
     */
    public function getDocumentIcon()
    {
        $extension = strtolower(pathinfo($this->document_file, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
        ];
        
        return $icons[$extension] ?? 'fas fa-file-alt text-secondary';
    }

    /**
     * Obtenir l'URL de l'actualité
     */
    public function getUrlAttribute()
    {
        return route('public.news.show', $this->id);
    }

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Incrémenter le compteur de téléchargements
     */
    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }

    /**
     * Scope pour les publications (actualités avec documents)
     */
    public function scopePublications($query)
    {
        return $query->whereNotNull('document_file')
                    ->where('document_file', '!=', '')
                    ->published();
    }

    /**
     * Obtenir les actualités liées
     */
    public function getRelatedNews($limit = 3)
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where('category', $this->category)
            ->recent($limit)
            ->get();
    }
}