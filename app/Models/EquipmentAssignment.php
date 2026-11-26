<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Affectation de matériel à un agent, suivie par le Comptable des
 * matières : sortie → affectee ; retour → retournee ; perte → perdue.
 * Toute modification passe par le portail/API comptable, jamais par
 * édition directe — la traçabilité (assigned_by, returned_by) est
 * systématiquement renseignée côté serveur.
 */
class EquipmentAssignment extends Model
{
    use HasFactory;

    public const STATUS_AFFECTEE = 'affectee';
    public const STATUS_RETOURNEE = 'retournee';
    public const STATUS_PERDUE = 'perdue';

    protected $fillable = [
        'warehouse_id',
        'produit',
        'unite',
        'quantity',
        'agent_name',
        'assigned_to',
        'numero_bon',
        'status',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'returned_by',
        'return_note',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_AFFECTEE);
    }
}
