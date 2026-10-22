<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mission opérationnelle affectée par le DTL :
 * - type=inspection : mission terrain pour un inspecteur
 * - type=stock_exit : instruction de sortie de stock pour un magasinier
 */
class RequestMission extends Model
{
    public const TYPE_INSPECTION = 'inspection';
    public const TYPE_STOCK_EXIT = 'stock_exit';

    public const STATUSES = ['affectee', 'en_cours', 'rapport_soumis', 'terminee', 'annulee'];

    protected $fillable = [
        'public_request_id', 'type', 'assigned_by', 'assigned_to',
        'instructions', 'status', 'report', 'attachments',
        'visit_latitude', 'visit_longitude', 'visited_at',
        'reported_at', 'completed_at',
        'review_comment', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'visited_at' => 'datetime',
        'reported_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(PublicRequest::class, 'public_request_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'affectee' => 'Affectée',
            'en_cours' => 'En cours',
            'rapport_soumis' => 'Rapport soumis',
            'terminee' => 'Terminée',
            'annulee' => 'Annulée',
            default => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_INSPECTION ? 'Inspection terrain' : 'Sortie de stock';
    }
}
