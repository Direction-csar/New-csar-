<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'bon_matiere_id', 'code', 'qr_data', 'used', 'used_at', 'used_by',
        'reissued_at', 'reissue_reason', 'reissued_by',
    ];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
        'reissued_at' => 'datetime',
    ];

    public function bonMatiere()
    {
        return $this->belongsTo(BonMatiere::class);
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function reissuedBy()
    {
        return $this->belongsTo(User::class, 'reissued_by');
    }
}
