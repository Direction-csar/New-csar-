<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReportExport extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'portal', 'report_type', 'filters', 'format', 'filename', 'path',
    ];

    protected $casts = ['filters' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
