<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Opération de stock encadrée par le circuit DG → DTL → Inspecteur → Magasinier.
 * Voir migration 2026_11_25_000000_create_stock_operations_table.
 */
class StockOperation extends Model
{
    use SoftDeletes;

    public const TYPE_ENTRY = 'entry';
    public const TYPE_EXIT = 'exit';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_INVENTORY = 'inventory';

    public const TYPES = [self::TYPE_ENTRY, self::TYPE_EXIT, self::TYPE_TRANSFER, self::TYPE_INVENTORY];

    public const STATUS_SOUMISE = 'soumise';
    public const STATUS_AFFECTEE = 'affectee_inspecteur';
    public const STATUS_TRANSMISE = 'transmise_magasinier';
    public const STATUS_EXECUTEE = 'executee';
    public const STATUS_VERIFIEE = 'verifiee';
    public const STATUS_ANOMALIE = 'anomalie';
    public const STATUS_REJETEE = 'rejetee';
    public const STATUS_ANNULEE = 'annulee';

    public const STATUTS = [
        self::STATUS_SOUMISE, self::STATUS_AFFECTEE, self::STATUS_TRANSMISE,
        self::STATUS_EXECUTEE, self::STATUS_VERIFIEE, self::STATUS_ANOMALIE,
        self::STATUS_REJETEE, self::STATUS_ANNULEE,
    ];

    /** Statuts qui bloquent toute nouvelle action sur l'opération. */
    public const STATUTS_FINAUX = [self::STATUS_VERIFIEE, self::STATUS_REJETEE, self::STATUS_ANNULEE];

    protected $fillable = [
        'reference', 'type', 'warehouse_id', 'destination_warehouse_id',
        'produit', 'unite', 'format',
        'quantity_requested', 'quantity_requested_kg',
        'quantity_executed', 'quantity_executed_kg',
        'instructions', 'dtl_comment', 'inspector_comment',
        'anomaly_report', 'rejection_reason', 'attachments',
        'status',
        'created_by', 'dtl_assigned_by', 'dtl_assigned_at',
        'assigned_inspector_id', 'inspector_transmitted_at',
        'assigned_magasinier_id', 'executed_by', 'executed_at',
        'verified_by', 'verified_at',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_requested_kg' => 'decimal:2',
        'quantity_executed' => 'decimal:2',
        'quantity_executed_kg' => 'decimal:2',
        'attachments' => 'array',
        'dtl_assigned_at' => 'datetime',
        'inspector_transmitted_at' => 'datetime',
        'executed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'stock_operation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dtlAssigner()
    {
        return $this->belongsTo(User::class, 'dtl_assigned_by');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'assigned_inspector_id');
    }

    public function magasinier()
    {
        return $this->belongsTo(User::class, 'assigned_magasinier_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ENTRY => 'Entrée de stock',
            self::TYPE_EXIT => 'Sortie de stock',
            self::TYPE_TRANSFER => 'Transfert',
            self::TYPE_INVENTORY => 'Inventaire',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SOUMISE => 'Soumise (DTL)',
            self::STATUS_AFFECTEE => 'Affectée à l\'inspecteur',
            self::STATUS_TRANSMISE => 'Transmise au magasinier',
            self::STATUS_EXECUTEE => 'Exécutée (à vérifier)',
            self::STATUS_VERIFIEE => 'Vérifiée',
            self::STATUS_ANOMALIE => 'Anomalie',
            self::STATUS_REJETEE => 'Rejetée',
            self::STATUS_ANNULEE => 'Annulée',
            default => $this->status,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::STATUTS_FINAUX, true);
    }
}
