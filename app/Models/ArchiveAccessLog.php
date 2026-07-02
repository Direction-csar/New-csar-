<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveAccessLog extends Model
{
    use HasFactory;

    protected $fillable = ['archive_id', 'user_id', 'action', 'ip_address', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function archive()
    {
        return $this->belongsTo(Archive::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
