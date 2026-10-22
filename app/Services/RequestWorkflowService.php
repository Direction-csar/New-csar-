<?php

namespace App\Services;

use App\Models\PublicRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Service métier unique pour les transitions du workflow des demandes publiques.
 *
 * Centralise les règles de transition, les autorisations par rôle, les motifs
 * obligatoires, l'audit des actions déléguées et la synchronisation du statut
 * legacy `status`. Ne pas dupliquer ces règles dans les contrôleurs.
 */
class RequestWorkflowService
{
    /**
     * Graphe des transitions autorisées sur workflow_status.
     */
    public const GRAPH = [
        'soumise'          => ['en_revue', 'document_attente', 'rejetee'],
        'en_revue'         => ['document_attente', 'signee', 'rejetee'],
        'document_attente' => ['en_revue', 'signee', 'rejetee'],
        'signee'           => ['scannee', 'document_attente', 'rejetee'],
        'scannee'          => ['validee_dg', 'rejetee'],
        'validee_dg'       => ['approuvee', 'rejetee'],
        'approuvee'        => ['cloturee'],
        'rejetee'          => [],
        'cloturee'         => [],
    ];

    /**
     * Correspondance workflow_status -> statut legacy `status`.
     */
    public const LEGACY_MAP = [
        'soumise'          => 'pending',
        'en_revue'         => 'pending',
        'document_attente' => 'pending',
        'signee'           => 'pending',
        'scannee'          => 'pending',
        'validee_dg'       => 'approved',
        'approuvee'        => 'approved',
        'rejetee'          => 'rejected',
        'cloturee'         => 'completed',
    ];

    /**
     * Rôles autorisés à déclencher chaque transition (par statut cible).
     *
     * La signature (« signee ») et la décision (« validee_dg », « approuvee »)
     * relèvent exclusivement de la Direction Générale : l'administrateur ne
     * peut ni signer ni approuver à sa place. L'admin garde les opérations
     * techniques (revue, demande de pièces, enregistrement du scan, clôture)
     * et le rejet en triage — voir la règle additionnelle dans advance().
     */
    public const ROLE_MAP = [
        'en_revue'         => ['admin', 'super_admin', 'dg', 'directeur_general'],
        'document_attente' => ['admin', 'super_admin'],
        'signee'           => ['signataire', 'dg', 'directeur_general'],
        'scannee'          => ['scanneur', 'dg', 'directeur_general', 'admin', 'super_admin'],
        'validee_dg'       => ['dg', 'directeur_general'],
        'approuvee'        => ['dg', 'directeur_general'],
        'rejetee'          => ['dg', 'directeur_general', 'admin', 'super_admin'],
        'cloturee'         => ['admin', 'super_admin', 'dg', 'directeur_general', 'dtl'],
    ];

    /**
     * Statuts cibles exigeant systématiquement un commentaire/motif.
     */
    public const COMMENT_REQUIRED = ['rejetee', 'document_attente'];

    /**
     * Étapes à partir desquelles le dossier relève de la décision de la
     * Direction : un admin ne peut plus le rejeter en triage.
     */
    public const DECISION_STAGES = ['signee', 'scannee', 'validee_dg'];

    /**
     * Transitions directes autorisées depuis un statut donné.
     */
    public function transitionsFrom(?string $from): array
    {
        return self::GRAPH[$from ?? 'soumise'] ?? [];
    }

    /**
     * Transitions directes autorisées depuis un statut pour un utilisateur donné.
     */
    public function allowedTransitionsFor(?string $from, ?User $user): array
    {
        $allowed = [];
        foreach ($this->transitionsFrom($from) as $to) {
            if ($this->canPerform($user, $to)) {
                $allowed[] = $to;
            }
        }
        return $allowed;
    }

    /**
     * L'utilisateur a-t-il le rôle requis pour la transition cible ?
     */
    public function canPerform(?User $user, string $to): bool
    {
        if (!$user) {
            return false;
        }
        return in_array($user->role, self::ROLE_MAP[$to] ?? [], true);
    }

    /**
     * Effectue une transition directe (un seul pas dans le graphe).
     *
     * @throws ValidationException si la transition, le rôle, le motif ou les
     *                             pièces justificatives requises manquent.
     */
    public function advance(PublicRequest $demande, string $to, ?string $comment, ?User $actor): PublicRequest
    {
        $from = $demande->workflow_status ?? 'soumise';
        $comment = $comment !== null ? trim($comment) : null;

        if ($from === $to) {
            return $demande;
        }

        if (!in_array($to, $this->transitionsFrom($from), true)) {
            throw ValidationException::withMessages([
                'workflow_status' => "Transition non autorisée : « {$from} » → « {$to} ». "
                    . 'Transitions possibles : ' . implode(', ', $this->transitionsFrom($from)),
            ]);
        }

        if (!$this->canPerform($actor, $to)) {
            throw ValidationException::withMessages([
                'workflow_status' => "Votre rôle ne permet pas la transition vers « {$to} ».",
            ]);
        }

        // Un admin peut rejeter en triage (avant signature) mais ne peut pas
        // annuler la décision ou la signature de la Direction.
        if ($to === 'rejetee'
            && in_array($actor?->role, ['admin', 'super_admin'], true)
            && in_array($from, self::DECISION_STAGES, true)) {
            throw ValidationException::withMessages([
                'workflow_status' => 'Seule la Direction Générale peut rejeter une demande signée ou en cours de décision.',
            ]);
        }

        if (in_array($to, self::COMMENT_REQUIRED, true) && empty($comment)) {
            throw ValidationException::withMessages([
                'comment' => 'Un motif/commentaire est obligatoire pour cette transition.',
            ]);
        }

        // Pièces justificatives obligatoires : pas de "signée" sans document,
        // pas de "scannée" sans scan.
        if ($to === 'signee' && empty($demande->dg_signature_file)) {
            throw ValidationException::withMessages([
                'signature_file' => 'Un document signé doit être enregistré avant de marquer la demande comme signée.',
            ]);
        }
        if ($to === 'scannee' && empty($demande->scan_file)) {
            throw ValidationException::withMessages([
                'scan_file' => 'Un scan du document doit être enregistré avant de marquer la demande comme scannée.',
            ]);
        }

        // Clôture : impossible tant qu'une mission est active ou non achevée
        if ($to === 'cloturee') {
            $demande->loadMissing('missions');
            $active = $demande->missions->whereNotIn('status', ['terminee', 'annulee']);
            if ($active->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'workflow_status' => 'Impossible de clôturer : ' . $active->count()
                        . ' mission(s) encore active(s) (inspection ou sortie de stock non achevée).',
                ]);
            }
        }

        return DB::transaction(function () use ($demande, $from, $to, $comment, $actor) {
            $demande->workflow_status = $to;
            $demande->status = self::LEGACY_MAP[$to] ?? $demande->status;
            if (in_array($to, ['validee_dg', 'approuvee', 'rejetee', 'cloturee'], true) && empty($demande->processed_date)) {
                $demande->processed_date = now()->toDateString();
            }
            $demande->save();

            $demande->logWorkflowAction("Transition: {$from} → {$to}", $comment, $actor?->id);

            // Transmission officielle au DTL dès que la demande est approuvée
            if ($to === 'approuvee' && empty($demande->dtl_transmitted_at)) {
                $demande->update([
                    'dtl_transmitted_at' => now(),
                    'dtl_transmitted_by' => $actor?->id,
                ]);
                $demande->logWorkflowAction('Transmise au DTL', 'Dossier approuvé transmis à la Direction Technique et Logistique', $actor?->id);
                try {
                    \App\Services\RequestNotificationService::notifyDtl($demande);
                } catch (\Throwable $e) {
                    Log::warning('Notification DTL échouée: ' . $e->getMessage());
                }
            }

            return $demande->fresh();
        });
    }

    /**
     * Décision de la Directrice : valide (scannee → validee_dg) ou rejette
     * (motif obligatoire). La validation exige le préalable signature + scan.
     */
    public function dgValidate(PublicRequest $demande, bool $approved, ?string $comment, ?User $actor): PublicRequest
    {
        if ($approved) {
            $demande = $this->advance($demande, 'validee_dg', $comment ?: 'Validée par la Direction Générale', $actor);
            $demande->update([
                'dg_approved_by' => $actor?->id,
                'dg_approved_at' => now(),
            ]);

            // La décision de la Directrice est la décision officielle : la
            // demande passe immédiatement à « approuvee », ce qui déclenche
            // la transmission effective au DTL (horodatage + notification).
            $demande = $this->advance($demande, 'approuvee', $comment, $actor);

            return $demande;
        }

        if (empty(trim((string) $comment))) {
            throw ValidationException::withMessages([
                'dg_comment' => 'Un motif est obligatoire pour rejeter la demande.',
            ]);
        }

        return $this->advance($demande, 'rejetee', $comment, $actor);
    }

    /**
     * Chemin de transitions (BFS) d'un statut vers un autre, ou null si
     * impossible. Utilisé uniquement pour les corrections administratives
     * auditées — les transitions de décision restent soumises aux règles
     * de rôle, de motif et de pièces jointes à chaque étape.
     */
    public function findPath(?string $from, string $to): ?array
    {
        $from = $from ?? 'soumise';
        if ($from === $to) {
            return [];
        }

        $queue = [[$from, []]];
        $visited = [$from => true];

        while ($queue) {
            [$current, $path] = array_shift($queue);
            foreach ($this->transitionsFrom($current) as $next) {
                if (isset($visited[$next])) {
                    continue;
                }
                $visited[$next] = true;
                if ($next === $to) {
                    return array_merge($path, [$next]);
                }
                $queue[] = [$next, array_merge($path, [$next])];
            }
        }

        return null;
    }
}
