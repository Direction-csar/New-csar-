<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhSalaryImport extends Model
{
    protected $table = 'rh_salary_imports';

    protected $fillable = [
        'filename', 'sha256', 'status', 'summary', 'rows', 'created_by', 'applied_by', 'applied_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'rows' => 'array',
        'applied_at' => 'datetime',
    ];

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applier()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
