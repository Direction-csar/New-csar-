<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'amount',
        'payment_method',
        'payment_provider',
        'payment_status',
        'transaction_id',
        'currency',
        'is_anonymous',
        'message',
        'donation_type',
        'frequency',
        'processed_at',
        'failed_at',
        'failure_reason',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function scopeSuccessful($query)
    {
        return $query->where('payment_status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    public function isSuccessful()
    {
        return $this->payment_status === 'success';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }
}
