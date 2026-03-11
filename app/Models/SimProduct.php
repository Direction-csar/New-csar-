<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimProduct extends Model
{
    use HasFactory;

    public const ORIGIN_LOCAL = 'local';
    public const ORIGIN_IMPORTED = 'imported';
    public const ORIGIN_BOTH = 'both';

    protected $fillable = [
        'sim_product_category_id',
        'name',
        'slug',
        'unit',
        'origin_type',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(SimProductCategory::class, 'sim_product_category_id');
    }

    public function collectionItems()
    {
        return $this->hasMany(SimCollectionItem::class, 'sim_product_id');
    }
}
