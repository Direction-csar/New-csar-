<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoublonDetecte extends Model
{
    use HasFactory;

    protected $table = 'doublon_detectes';

    protected $fillable = [
        'type', 'entity_1_id', 'entity_2_id', 'planning_1_id', 'planning_2_id',
        'status', 'justification', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function entity1()
    {
        return $this->belongsTo(Beneficiaire::class, 'entity_1_id');
    }

    public function entity2()
    {
        return $this->belongsTo(Beneficiaire::class, 'entity_2_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
