<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvanceTabaski extends Model
{
    protected $table = 'avance_tabaski';

    protected $fillable = ['agent_id', 'montant', 'ip_address', 'date_inscription'];

    protected $casts = ['date_inscription' => 'datetime'];

    public function agent()
    {
        return $this->belongsTo(AgentTabaski::class, 'agent_id');
    }

    public function getMontantFormatteAttribute(): string
    {
        return number_format((int) $this->montant, 0, ',', ' ') . ' FCFA';
    }
}
