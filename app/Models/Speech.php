<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speech extends Model
{
    use HasFactory;

    protected $fillable = [
        'author',
        'function',
        'title',
        'excerpt',
        'content',
        'date',
        'location',
        'summary',
        'portrait',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
