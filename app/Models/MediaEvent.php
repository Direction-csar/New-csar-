<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MediaEvent extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_date',
        'cover_image',
        'status',
        'views',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'views' => 'integer',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MediaFile::class)->where('type', 'image');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(MediaFile::class)->where('type', 'video');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(MediaDownload::class);
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'album';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
