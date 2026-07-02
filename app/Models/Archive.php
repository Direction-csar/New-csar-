<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archive extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'title', 'description', 'direction', 'folder_id',
        'annee', 'file_path', 'file_name', 'file_size', 'mime_type',
        'page_count', 'created_by',
    ];

    public function folder()
    {
        return $this->belongsTo(ArchiveFolder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function accessLogs()
    {
        return $this->hasMany(ArchiveAccessLog::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($archive) {
            $count = static::where('direction', $archive->direction)->withTrashed()->count() + 1;
            $archive->reference = 'CSAR-ARCH-' . strtoupper($archive->direction) . '-' . $archive->annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }
}
