<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'region',
        'city',
        'phone',
        'email',
        'is_active',
        'capacity',
        'current_stock',
        'status'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'capacity' => 'float',
        'current_stock' => 'float'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Relation avec les utilisateurs (responsables)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec les mouvements de stock
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /**
     * Accessor pour capacite (alias de capacity pour compatibilité avec les vues)
     */
    public function getCapaciteAttribute()
    {
        return $this->capacity ?? 0;
    }

    /**
     * Mutator pour capacite (redirige vers capacity)
     */
    public function setCapaciteAttribute($value)
    {
        $this->attributes['capacity'] = $value;
    }

    /**
     * Accessors pour les anciens noms de colonnes (compatibilité)
     */
    public function getNomAttribute()
    {
        return $this->name;
    }

    public function getAdresseAttribute()
    {
        return $this->address;
    }

    public function getVilleAttribute()
    {
        return $this->city;
    }

    public function getStatutAttribute()
    {
        return $this->status;
    }

    /**
     * Mutators pour les anciens noms de colonnes (compatibilité)
     */
    public function setNomAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    public function setAdresseAttribute($value)
    {
        $this->attributes['address'] = $value;
    }

    public function setVilleAttribute($value)
    {
        $this->attributes['city'] = $value;
    }

    public function setStatutAttribute($value)
    {
        $this->attributes['status'] = $value;
    }
} 