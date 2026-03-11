<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimCollectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_collection_id',
        'sim_product_id',
        'quantity_observed',
        'price_producer',
        'price_retail',
        'price_wholesale',
        'offer_quantity',
        'stock_quantity',
        'origin_label',
        'notes',
    ];

    protected $casts = [
        'quantity_observed' => 'decimal:2',
        'price_producer' => 'decimal:2',
        'price_retail' => 'decimal:2',
        'price_wholesale' => 'decimal:2',
        'offer_quantity' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
    ];

    public function collection()
    {
        return $this->belongsTo(SimCollection::class, 'sim_collection_id');
    }

    public function product()
    {
        return $this->belongsTo(SimProduct::class, 'sim_product_id');
    }
}
