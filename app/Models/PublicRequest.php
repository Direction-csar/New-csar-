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
        'dtl_transmitted_at',
        'dtl_transmitted_by',
        'dtl_instructions',
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

    public function originalRequest()
    {
        return $this->belongsTo(self::class, 'duplicate_of');
    }

    public function duplicates()
    {
        return $this->hasMany(self::class, 'duplicate_of');
    }

    public function missions()
    {
        return $this->hasMany(RequestMission::class, 'public_request_id');
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
        $key = strtolower(trim($data['full_name'])) . '|' . ltrim(self::normalizePhone($data['phone']), '+');
        return hash('sha256', $key);
    }

    /**
     * Vérifier si une demande similaire existe déjà (anti-duplication)
     */
    public static function checkDuplicate(array $data, int $hours = 24): ?self
    {
        $requesterId = self::generateRequesterId($data);
        $subject = strtolower(trim($data['subject']));

        // Retain compatibility with legacy fingerprints (9 digits without +221).
        $legacyKey = strtolower(trim($data['full_name'])).'|'.preg_replace('/[^0-9]/', '', $data['phone']);
        $nationalKey = strtolower(trim($data['full_name'])).'|'.preg_replace('/^221/', '', ltrim(self::normalizePhone($data['phone']), '+'));
        return self::whereIn('requester_id', array_unique([$requesterId, hash('sha256', $legacyKey), hash('sha256', $nationalKey)]))
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
    public function logWorkflowAction(string $action, ?string $comment = null, ?int $userId = null, array $details = []): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($action, $comment, $userId, $details) {
            $locked = self::whereKey($this->id)->lockForUpdate()->firstOrFail();
            $history = $locked->workflow_history ?? [];
            $history[] = array_merge([
                'id' => (string) Str::uuid(),
                'action' => $action,
                'comment' => $comment,
                'user_id' => $userId ?? auth()->id(),
                'timestamp' => now()->toIso8601String(),
            ], $details);
            $query = self::whereKey($this->id)->where('workflow_history', $locked->getRawOriginal('workflow_history'));
            if (!$query->update(['workflow_history' => json_encode($history, JSON_UNESCAPED_UNICODE)])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['workflow_status' => 'Le dossier a changé. Rechargez-le.']);
            }
            $this->refresh();
        });
    }

    /**
     * Avancer le workflow à l'étape suivante
     */
    public function advanceWorkflow(string $newStatus, ?string $comment = null): void
    {
        app(\App\Services\RequestWorkflowService::class)->advance($this, $newStatus, $comment, auth()->user());
        $this->refresh();
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 9) {
            $digits = '221'.$digits;
        }
        return '+'.$digits;
    }

    public static function publicStatusLabel(?string $status): string
    {
        return [
            'soumise' => 'Demande reçue',
            'en_revue' => 'En cours d’étude',
            'document_attente' => 'Complément demandé',
            'signee' => 'Document signé',
            'scannee' => 'Document enregistré',
            'validee_dg' => 'Validée par la Direction générale',
            'approuvee' => 'Approuvée — exécution à organiser',
            'rejetee' => 'Rejetée',
            'cloturee' => 'Clôturée',
        ][$status] ?? 'État à confirmer auprès du CSAR';
    }

    public function publicSummary(): object
    {
        $timeline = [];
        if ($this->created_at) {
            $timeline[] = ['label' => self::publicStatusLabel('soumise'), 'timestamp' => $this->created_at->toIso8601String(), 'reason' => null];
        }
        $reason = null;
        foreach ($this->workflow_history ?? [] as $entry) {
            $to = $entry['to_status'] ?? null;
            // Legacy transitions: recover only the controlled state name, never free text.
            if (!$to && preg_match('/^Transition: [a-z_]+ → ([a-z_]+)$/u', $entry['action'] ?? '', $match)) {
                $to = $match[1];
            }
            $label = isset(\App\Services\RequestWorkflowService::GRAPH[$to ?? '']) ? self::publicStatusLabel($to) : null;
            if (!$label) {
                $label = [
                    'Transmise au DTL' => 'Transmise pour exécution',
                    'Instruction DTL' => 'Instruction opérationnelle enregistrée',
                    'Mission affectée' => 'Mission affectée',
                    'Mission démarrée' => 'Mission en cours',
                    'Rapport inspection soumis' => 'Rapport de mission soumis',
                    'Rapport validé par le DTL' => 'Rapport de mission validé',
                    'Correction demandée par le DTL' => 'Rapport de mission à compléter',
                    'Mission annulée' => 'Mission annulée',
                ][$entry['action'] ?? ''] ?? null;
            }
            if (!$label || empty($entry['timestamp'])) {
                continue;
            }
            $publicReason = in_array($to, ['rejetee', 'document_attente'], true) ? ($entry['public_comment'] ?? null) : null;
            $timeline[] = ['label' => $label, 'timestamp' => $entry['timestamp'], 'reason' => $publicReason];
            if ($to === $this->workflow_status) {
                $reason = $publicReason;
            }
        }
        return (object) [
            'tracking_code' => $this->tracking_code, 'full_name' => $this->full_name,
            'email' => $this->email, 'phone' => self::normalizePhone((string) $this->phone),
            'subject' => $this->subject, 'type' => $this->type,
            'address' => $this->address, 'region' => $this->region,
            'description' => $this->description, 'created_at' => $this->created_at,
            'date_traitement' => $this->processed_date,
            'latitude' => $this->latitude, 'longitude' => $this->longitude,
            'status' => $this->workflow_status, 'status_label' => self::publicStatusLabel($this->workflow_status),
            'public_reason' => $reason, 'timeline' => $timeline,
        ];
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
