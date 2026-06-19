<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    protected $fillable = [
        'media_event_id',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'downloads',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'downloads' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(MediaEvent::class, 'media_event_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' Ko';
        return $bytes . ' o';
    }
}
