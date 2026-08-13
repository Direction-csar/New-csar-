<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiaire extends Model
{
    use HasFactory;

    protected $table = 'beneficiaires';

    protected $fillable = [
        'planning_id', 'name', 'phone', 'cni', 'address', 'category',
        'vulnerable', 'religious', 'spontaneous', 'created_by', 'status',
    ];

    protected $casts = [
        'vulnerable' => 'boolean',
        'religious' => 'boolean',
        'spontaneous' => 'boolean',
    ];

    public function planning()
    {
        return $this->belongsTo(Planning::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bonMatieres()
    {
        return $this->hasMany(BonMatiere::class);
    }
}
