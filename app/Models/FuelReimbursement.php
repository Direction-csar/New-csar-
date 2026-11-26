<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Remboursement de carburant avancé par un agent.
 * IMPORTANT : ce flux ne touche JAMAIS le stock physique — le carburant
 * a été acheté directement par l'agent (station-service).
 *
 * Statuts : draft → submitted → in_review → approved → paid → closed
 *                  \→ rejected
 * Régularisation d'avance : advance_amount vs justified_amount,
 * complement_amount = complément payé (positif) ou reliquat restitué (négatif).
 */
class FuelReimbursement extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'reference', 'agent_name', 'agent_id', 'service', 'expense_date',
        'purpose', 'activity_ref', 'activity_type',
        'vehicle_label', 'vehicle_plate',
        'amount_claimed', 'quantity_liters', 'receipt_ref',
        'amount_requested', 'amount_approved', 'amount_paid',
        'advance_amount', 'justified_amount', 'complement_amount',
        'payment_ref', 'payment_date',
        'status', 'created_by', 'reviewed_by', 'approved_by', 'approved_at',
        'paid_by', 'paid_at', 'review_comment', 'rejection_reason',
        'duplicate_of_ref', 'idempotency_key',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'payment_date' => 'date',
        'amount_claimed' => 'decimal:2',
        'quantity_liters' => 'decimal:2',
        'amount_requested' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'justified_amount' => 'decimal:2',
        'complement_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function attachments()
    {
        return $this->morphMany(FuelAttachment::class, 'attachable')->latest();
    }

    public function statusLogs()
    {
        return $this->morphMany(FuelStatusLog::class, 'subject')->latest();
    }

    /** Montant restant à justifier sur l'avance (avances & régularisations). */
    public function remainingToJustify(): ?float
    {
        if ($this->advance_amount === null) {
            return null;
        }
        return round((float) $this->advance_amount - (float) ($this->justified_amount ?? 0), 2);
    }

    /** État synthétique de la régularisation d'avance. */
    public function regularizationState(): string
    {
        if ($this->advance_amount === null) {
            return 'sans_avance';
        }
        $rest = $this->remainingToJustify();
        if ($rest > 0.005) {
            return 'a_justifier';
        }
        if ($rest < -0.005) {
            return 'complement_du';
        }
        return 'soldee';
    }
}
