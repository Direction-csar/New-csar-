<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'unit',
        'description',
        'unit_price',
        'category',
        'image',
        'is_active'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relations
    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'produit', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return $this->unit_price ? number_format($this->unit_price, 0, ',', ' ') . ' FCFA' : 'Non défini';
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->unit . ')';
    }
}
