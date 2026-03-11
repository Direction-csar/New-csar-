<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'description',
        'summary',
        'report_type',
        'period_start',
        'period_end',
        'status',
        'document_file',
        'cover_image', // Image de couverture du rapport
        'is_public', // Visibilité publique
        'created_by',
        'generated_by',
        'generated_at',
        'scheduled_at',
        'published_at',
        'download_count',
        'view_count',
        'file_size',
        'metadata'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'file_size' => 'integer',
        'is_public' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Obtenir l'utilisateur qui a généré le rapport
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('metadata->region', $region);
    }

    public function scopeBySector($query, $sector)
    {
        return $query->where('metadata->sector', $sector);
    }

    /**
     * Scope pour les rapports publiés
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope pour les rapports terminés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les rapports en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les rapports programmés
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope pour les rapports publics (publiés ET visibles)
     */
    public function scopePublic($query)
    {
        return $query->where('status', 'published')
                    ->where('is_public', true)
                    ->where('published_at', '<=', now());
    }

    /**
     * Obtenir l'URL de téléchargement du document
     */
    public function getDownloadUrlAttribute()
    {
        if ($this->document_file) {
            return route('admin.sim-reports.download', $this->id);
        }
        return null;
    }

    /**
     * Obtenir l'URL publique de téléchargement
     */
    public function getPublicDownloadUrlAttribute()
    {
        if ($this->document_file && $this->status === 'published' && $this->is_public) {
            return route('sim.download', $this->id);
        }
        return null;
    }

    public function getReportTypeLabelAttribute()
    {
        return match ($this->report_type) {
            'daily' => 'Quotidien',
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
            'quarterly' => 'Trimestriel',
            'annual' => 'Annuel',
            'special' => 'Spécial',
            default => ucfirst((string) $this->report_type),
        };
    }

    public function getSectorLabelAttribute()
    {
        return $this->metadata['sector'] ?? 'Marché';
    }

    public function getRegionAttribute()
    {
        return $this->metadata['region'] ?? null;
    }

    public function getFormattedPeriodAttribute()
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('d/m/Y') . ' - ' . $this->period_end->format('d/m/Y');
        }

        if ($this->period_start) {
            return $this->period_start->format('d/m/Y');
        }

        return 'Non spécifiée';
    }

    public function isPublic(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->is_public === true;
    }

    /**
     * Obtenir l'URL de l'image de couverture
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('images/default-report.jpg');
    }

    /**
     * Obtenir la taille du fichier formatée
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Incrémenter le compteur de téléchargements
     */
    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    public function incrementDownloadCount()
    {
        $this->incrementDownloads();
    }

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementViews()
    {
        $this->increment('view_count');
    }

    public function incrementViewCount()
    {
        $this->incrementViews();
    }

    /**
     * Marquer le rapport comme publié
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    /**
     * Marquer le rapport comme terminé
     */
    public function complete($documentFile = null, $fileSize = null)
    {
        $this->update([
            'status' => 'completed',
            'generated_at' => now(),
            'document_file' => $documentFile,
            'file_size' => $fileSize
        ]);
    }
}