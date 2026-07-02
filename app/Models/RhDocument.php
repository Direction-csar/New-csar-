<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RhDocument extends Model
{
    use HasFactory;

    protected $table = 'rh_documents';

    protected $fillable = [
        'personnel_id',
        'type',
        'label',
        'data',
        'statut',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
