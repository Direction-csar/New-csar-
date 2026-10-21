<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'warehouse_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'reference',
        'created_by',
        'numero_transfert',
        'entrepot_destination_id',
        'raison_ajustement',
        'metadata'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2'
    ];

    /**
     * Relation avec l'entrepôt
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relation avec le stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope pour filtrer par entrepôt
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope pour filtrer par type de mouvement
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getQuantityFormattedAttribute()
    {
        return number_format($this->quantity, 0, ',', ' ');
    }

    public function getTypeFormattedAttribute()
    {
        return ucfirst($this->type);
    }
}
