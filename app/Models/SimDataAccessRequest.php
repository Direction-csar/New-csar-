<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimDataAccessRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RESTRICTED = 'restricted';

    protected $fillable = [
        'requester_name',
        'organization',
        'role_title',
        'email',
        'phone',
        'request_subject',
        'requested_scope',
        'requested_data_types',
        'period_start',
        'period_end',
        'purpose',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'expires_at',
    ];

    protected $casts = [
        'requested_data_types' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
