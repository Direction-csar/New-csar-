<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublicRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tracking_code',
        'type',
        'status',
        'workflow_status',
        'full_name',
        'phone',
        'email',
        'subject',
        'address',
        'latitude',
        'longitude',
        'region',
        'description',
        'admin_comment',
        'assigned_to',
        'processed_by',
        'dg_approved_by',
        'dg_approved_at',
        'request_date',
        'processed_date',
        'sms_sent',
        'is_viewed',
        'viewed_at',
        'duplicate_hash',
        'requester_id',
        'duplicate_of',
        'is_duplicate',
        'urgency',
        'preferred_contact',
        'ip_address',
        'user_agent',
        'courier_reference',
        'courier_date',
        'dg_signature_file',
        'scan_file',
        'document_notes',
        'workflow_history',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'request_date' => 'date',
        'processed_date' => 'date',
        'sms_sent' => 'boolean',
        'is_viewed' => 'boolean',
        'viewed_at' => 'datetime',
        'dg_approved_at' => 'datetime',
        'courier_date' => 'date',
        'is_duplicate' => 'boolean',
        'workflow_history' => 'array',
    ];

    /**
     * Marquer la demande comme vue si ce n'est pas déjà fait
     */
    public function markAsViewed(): void
    {
        if (!$this->is_viewed) {
            $this->update([
                'is_viewed' => true,
                'viewed_at' => now(),
            ]);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function dgApprover()
    {
        return $this->belongsTo(User::class, 'dg_approved_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function originalRequest()
    {
        return $this->belongsTo(self::class, 'duplicate_of');
    }

    public function duplicates()
    {
        return $this->hasMany(self::class, 'duplicate_of');
    }

    public static function generateTrackingCode()
    {
        do {
            $code = 'CSAR-' . strtoupper(Str::random(12));
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * Générer un requester_id unique basé sur les infos du demandeur
     */
    public static function generateRequesterId(array $data): string
    {
        $key = strtolower(trim($data['full_name'])) . '|' . preg_replace('/[^0-9]/', '', $data['phone']);
        return hash('sha256', $key);
    }

    /**
     * Vérifier si une demande similaire existe déjà (anti-duplication)
     */
    public static function checkDuplicate(array $data, int $hours = 24): ?self
    {
        $requesterId = self::generateRequesterId($data);
        $subject = strtolower(trim($data['subject']));

        return self::where('requester_id', $requesterId)
            ->where('status', '!=', 'rejected')
            ->where('created_at', '>=', now()->subHours($hours))
            ->where(function ($q) use ($subject) {
                $q->whereRaw('LOWER(subject) LIKE ?', ['%' . $subject . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $subject . '%']);
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Enregistrer une action dans l'historique du workflow
     */
    public function logWorkflowAction(string $action, ?string $comment = null, ?int $userId = null): void
    {
        $history = $this->workflow_history ?? [];
        $history[] = [
            'action' => $action,
            'comment' => $comment,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['workflow_history' => $history]);
    }

    /**
     * Avancer le workflow à l'étape suivante
     */
    public function advanceWorkflow(string $newStatus, ?string $comment = null): void
    {
        $oldStatus = $this->workflow_status;
        $this->update(['workflow_status' => $newStatus]);
        $this->logWorkflowAction(
            "Transition: {$oldStatus} → {$newStatus}",
            $comment
        );
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'completed' => 'blue',
            default => 'gray'
        };
    }

    public function getWorkflowStatusLabelAttribute(): string
    {
        return match($this->workflow_status) {
            'soumise' => 'Soumise',
            'en_revue' => 'En revue',
            'document_attente' => 'Attente document',
            'signee' => 'Signée',
            'scannee' => 'Scannée',
            'validee_dg' => 'Validée DG',
            'approuvee' => 'Approuvée',
            'rejetee' => 'Rejetée',
            'cloturee' => 'Clôturée',
            default => $this->workflow_status,
        };
    }

    public function getWorkflowStatusBadgeAttribute(): string
    {
        $color = match($this->workflow_status) {
            'soumise' => 'secondary',
            'en_revue' => 'info',
            'document_attente' => 'warning',
            'signee' => 'primary',
            'scannee' => 'primary',
            'validee_dg' => 'success',
            'approuvee' => 'success',
            'rejetee' => 'danger',
            'cloturee' => 'dark',
            default => 'light',
        };
        return '<span class="badge bg-' . $color . '">' . $this->workflow_status_label . '</span>';
    }

    public function getIsSignedAttribute(): bool
    {
        return in_array($this->workflow_status, ['signee', 'scannee', 'validee_dg', 'approuvee', 'cloturee']);
    }

    public function getIsScannedAttribute(): bool
    {
        return in_array($this->workflow_status, ['scannee', 'validee_dg', 'approuvee', 'cloturee']);
    }

    public function getIsDgApprovedAttribute(): bool
    {
        return in_array($this->workflow_status, ['validee_dg', 'approuvee', 'cloturee']);
    }
}
