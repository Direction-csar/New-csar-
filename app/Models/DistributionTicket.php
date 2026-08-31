<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionTicket extends Model
{
    use HasFactory;

    public const STATUS_ISSUED = 'issued';
    public const STATUS_SCANNED = 'scanned';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'beneficiary_id', 'planning_id',
        'ticket_code', 'qr_token',
        'status', 'issued_at', 'scanned_at', 'collected_at',
        'scanned_by', 'scan_location',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'scanned_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(DistributionBeneficiary::class, 'beneficiary_id');
    }

    public function planning(): BelongsTo
    {
        return $this->belongsTo(DistributionPlanning::class, 'planning_id');
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(DistributionScanLog::class, 'ticket_id');
    }
}
