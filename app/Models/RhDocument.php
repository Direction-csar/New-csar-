<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RhDocument extends Model
{
    use HasFactory;

    public const CONTRACT_TYPES = ['contrat-cdi', 'contrat-cdd'];

    public const STATUT_A_VALIDER = 'Avenant à valider';
    public const STATUT_SIGNE = 'Signé';
    public const STATUT_REMPLACE = 'Remplacé';

    protected $table = 'rh_documents';

    protected $fillable = [
        'personnel_id',
        'type',
        'label',
        'data',
        'statut',
        'version',
        'file_path',
        'signed_at',
        'signed_by',
        'superseded_at',
        'exported_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'version' => 'integer',
        'signed_at' => 'datetime',
        'superseded_at' => 'datetime',
        'exported_at' => 'datetime',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function scopeContracts(Builder $query): Builder
    {
        return $query->whereIn('type', self::CONTRACT_TYPES);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereNull('superseded_at');
    }

    public function isContract(): bool
    {
        return in_array($this->type, self::CONTRACT_TYPES, true);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    public function isPendingValidation(): bool
    {
        return $this->statut === self::STATUT_A_VALIDER;
    }
}
