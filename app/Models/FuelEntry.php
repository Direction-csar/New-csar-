<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Approvisionnement en carburant (entrée de stock en litres).
 * Statuts : draft → validated | rejected | cancelled.
 * La ligne stock_movements n'est écrite qu'à la validation.
 */
class FuelEntry extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference', 'operation_date', 'warehouse_id', 'supplier',
        'fuel_type', 'fuel_type_label', 'quantity', 'unit_price', 'total_amount',
        'invoice_number', 'invoice_date', 'delivery_note_ref', 'funding_source',
        'notes', 'status', 'created_by', 'validated_by', 'validated_at',
        'rejection_reason', 'stock_movement_id', 'idempotency_key',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'invoice_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    public const FUEL_TYPES = ['essence', 'gasoil', 'petrole', 'autre'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function attachments()
    {
        return $this->morphMany(FuelAttachment::class, 'attachable')->latest();
    }

    public function statusLogs()
    {
        return $this->morphMany(FuelStatusLog::class, 'subject')->latest();
    }

    public function fuelLabel(): string
    {
        return $this->fuel_type === 'autre' && $this->fuel_type_label
            ? $this->fuel_type_label
            : ucfirst($this->fuel_type);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
