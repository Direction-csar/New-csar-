<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sortie de carburant / dotation chauffeur-véhicule.
 * Statuts : draft → authorized → validated | rejected | cancelled.
 * quantity_authorized : plafond accordé ; quantity_delivered : litres
 * réellement sortis — c'est cette quantité qui diminue le stock à la
 * validation (jamais au-delà du stock disponible ni de l'autorisation).
 */
class FuelExit extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'exit_number', 'operation_date', 'warehouse_id',
        'fuel_type', 'fuel_type_label',
        'beneficiary_name', 'beneficiary_id',
        'vehicle_label', 'vehicle_plate', 'destination', 'purpose',
        'mission_number', 'mission_start_date', 'mission_end_date',
        'authorization_ref',
        'quantity_requested', 'quantity_authorized', 'quantity_delivered',
        'odometer_start', 'odometer_end', 'delivered_by_name', 'notes',
        'status', 'created_by', 'authorized_by', 'authorized_at',
        'validated_by', 'validated_at', 'rejection_reason',
        'stock_movement_id', 'idempotency_key',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'mission_start_date' => 'date',
        'mission_end_date' => 'date',
        'quantity_requested' => 'decimal:2',
        'quantity_authorized' => 'decimal:2',
        'quantity_delivered' => 'decimal:2',
        'authorized_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
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
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_AUTHORIZED], true);
    }
}
