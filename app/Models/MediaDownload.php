<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaDownload extends Model
{
    protected $fillable = [
        'media_event_id',
        'media_file_id',
        'ip_address',
        'kind',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(MediaEvent::class, 'media_event_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }
}
