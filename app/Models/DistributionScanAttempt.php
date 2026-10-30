<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionScanAttempt extends Model
{
    public const ACTION_VERIFY = 'verify';
    public const ACTION_COLLECT = 'collect';

    public const RESULT_OK = 'ok';
    public const RESULT_INVALID_CODE = 'invalid_code';
    public const RESULT_FORBIDDEN = 'forbidden';
    public const RESULT_ALREADY_USED = 'already_used';
    public const RESULT_CANCELLED = 'cancelled';
    public const RESULT_PLANNING_INACTIVE = 'planning_inactive';
    public const RESULT_INCONSISTENT = 'inconsistent';

    protected $fillable = [
        'user_id', 'ticket_id', 'planning_id', 'beneficiary_id',
        'action', 'result', 'code', 'message', 'device_info', 'ip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(DistributionTicket::class, 'ticket_id');
    }

    public function planning(): BelongsTo
    {
        return $this->belongsTo(DistributionPlanning::class, 'planning_id');
    }
}
