<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveFolder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'direction', 'parent_id', 'created_by'];

    public function archives()
    {
        return $this->hasMany(Archive::class, 'folder_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
