<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionScanLog extends Model
{
    use HasFactory;

    public const ACTION_SCAN = 'scan';
    public const ACTION_COLLECT = 'collect';
    public const ACTION_CANCEL = 'cancel';

    protected $fillable = [
        'ticket_id', 'user_id', 'action', 'notes', 'device_info',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(DistributionTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
