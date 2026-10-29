<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Pièce justificative rattachée à une opération carburant
 * (entrée, sortie ou remboursement). Stockée sur le disque
 * « fuel_attachments » (hors public/) : l'accès passe toujours
 * par le contrôleur qui vérifie le rôle.
 */
class FuelAttachment extends Model
{
    public const DISK = 'fuel_attachments';
    public const MAX_SIZE_KB = 8192; // 8 Mo
    public const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    protected $fillable = [
        'attachable_type', 'attachable_id', 'original_name',
        'stored_path', 'mime_type', 'size', 'sha256', 'kind', 'uploaded_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function sizeHuman(): string
    {
        if ($this->size >= 1048576) {
            return round($this->size / 1048576, 1) . ' Mo';
        }
        return round($this->size / 1024, 0) . ' Ko';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }
}
