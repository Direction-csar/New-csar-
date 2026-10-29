<?php

namespace App\Services\Comptable;

use App\Models\AuditLog;
use App\Models\FuelAttachment;
use App\Models\FuelEntry;
use App\Models\FuelExit;
use App\Models\FuelReimbursement;
use App\Models\FuelStatusLog;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Règles métier du module carburant — source unique partagée entre
 * le portail web /comptable et l'API mobile /api/comptable/v1.
 *
 * Principes :
 *  - stock physique = stock_movements (unite 'L'), écrit UNIQUEMENT à
 *    la validation d'une entrée ou d'une sortie ;
 *  - les remboursements n'altèrent jamais le stock ;
 *  - toute transition de statut est journalisée (fuel_status_logs +
 *    audit_logs) avec acteur, horodatage et motif obligatoire pour
 *    les rejets ;
 *  - idempotence : idempotency_key unique à la création ; les
 *    transitions sont ré-entrant-safe (double clic = même résultat,
 *    pas de double écriture de stock).
 */
class FuelService
{
    /* ===================== RÉFÉRENCES ===================== */

    /**
     * Référence séquentielle par année : ENT-CARB-2026-00042.
     * Verrou de ligne sur une table de séquence dédiée via
     * DB::table(...)->lockForUpdate() n'existe pas : on utilise un
     * comptage MAX+1 dans la transaction de création + contrainte
     * UNIQUE en base (filet de sécurité anti-collision).
     */
    private function nextReference(string $prefix, string $table, string $column): string
    {
        $year = now()->year;
        $pattern = "{$prefix}-{$year}-%";

        $last = DB::table($table)
            ->where($column, 'like', $pattern)
            ->lockForUpdate()
            ->max($column);

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return sprintf('%s-%d-%05d', $prefix, $year, $seq);
    }

    /* ===================== ENTRÉES ===================== */

    /**
     * Créer un brouillon d'entrée de carburant.
     * @throws \InvalidArgumentException si l'opération existe déjà (idempotence)
     */
    public function createEntry(array $data, User $actor, ?string $idempotencyKey = null): FuelEntry
    {
        if ($idempotencyKey) {
            $existing = FuelEntry::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $actor, $idempotencyKey) {
            $quantity = round((float) $data['quantity'], 2);
            $unitPrice = round((float) $data['unit_price'], 2);

            $entry = FuelEntry::create([
                'reference'         => $this->nextReference('ENT-CARB', 'fuel_entries', 'reference'),
                'operation_date'    => $data['operation_date'],
                'warehouse_id'      => $data['warehouse_id'],
                'supplier'          => $data['supplier'],
                'fuel_type'         => $data['fuel_type'],
                'fuel_type_label'   => $data['fuel_type_label'] ?? null,
                'quantity'          => $quantity,
                'unit_price'        => $unitPrice,
                'total_amount'      => round($quantity * $unitPrice, 2),
                'invoice_number'    => $data['invoice_number'] ?? null,
                'invoice_date'      => $data['invoice_date'] ?? null,
                'delivery_note_ref' => $data['delivery_note_ref'] ?? null,
                'funding_source'    => $data['funding_source'] ?? null,
                'notes'             => $data['notes'] ?? null,
                'status'            => FuelEntry::STATUS_DRAFT,
                'created_by'        => $actor->id,
                'idempotency_key'   => $idempotencyKey,
            ]);

            $this->logTransition($entry, 'created', null, FuelEntry::STATUS_DRAFT, null, $actor);

            return $entry;
        });
    }

    /**
     * Valider une entrée : écrit la ligne stock_movements (unite 'L',
     * statut validated) — c'est le seul endroit où le stock augmente.
     */
    public function validateEntry(FuelEntry $entry, User $actor): array
    {
        if ($entry->status === FuelEntry::STATUS_VALIDATED) {
            return ['ok' => true, 'message' => "Entrée {$entry->reference} déjà validée.", 'entry' => $entry];
        }
        if ($entry->status !== FuelEntry::STATUS_DRAFT) {
            return ['ok' => false, 'message' => "Une entrée {$entry->status} ne peut pas être validée."];
        }

        return DB::transaction(function () use ($entry, $actor) {
            // Verrou pessimiste : deux validations concurrentes se
            // sérialisent ; la seconde lit le statut déjà validé.
            $locked = FuelEntry::lockForUpdate()->find($entry->id);
            if ($locked->status === FuelEntry::STATUS_VALIDATED) {
                return ['ok' => true, 'message' => "Entrée {$locked->reference} déjà validée.", 'entry' => $locked];
            }
            if ($locked->status !== FuelEntry::STATUS_DRAFT) {
                return ['ok' => false, 'message' => "Une entrée {$locked->status} ne peut pas être validée."];
            }

            $produit = 'Carburant — ' . $locked->fuelLabel();
            $movement = StockMovement::create([
                'warehouse_id' => $locked->warehouse_id,
                'type'         => 'entry',
                'produit'      => $produit,
                'unite'        => 'L',
                'quantity'     => $locked->quantity,
                'prix_unitaire' => $locked->unit_price,
                'total'        => $locked->total_amount,
                'reference'    => $locked->reference,
                'fournisseur'  => $locked->supplier,
                'numero_facture' => $locked->invoice_number,
                'numero_bon'   => $locked->delivery_note_ref,
                'date_mouvement' => $locked->operation_date,
                'reason'       => 'Approvisionnement carburant',
                'motif'        => $locked->funding_source,
                'created_by'   => $actor->id,
                'status'       => 'validated',
                'validated_by' => $actor->id,
                'validated_at' => now(),
                'metadata'     => json_encode(['fuel_entry_id' => $locked->id]),
            ]);

            $locked->update([
                'status'             => FuelEntry::STATUS_VALIDATED,
                'validated_by'       => $actor->id,
                'validated_at'       => now(),
                'stock_movement_id'  => $movement->id,
            ]);

            $this->logTransition($locked, 'validated', FuelEntry::STATUS_DRAFT,
                FuelEntry::STATUS_VALIDATED, null, $actor);

            return ['ok' => true, 'message' => "Entrée {$locked->reference} validée — stock crédité de {$locked->quantity} L.", 'entry' => $locked->fresh()];
        });
    }

    /** Rejeter une entrée en brouillon (motif obligatoire). */
    public function rejectEntry(FuelEntry $entry, string $reason, User $actor): array
    {
        if ($entry->status !== FuelEntry::STATUS_DRAFT) {
            return ['ok' => false, 'message' => 'Seul un brouillon peut être rejeté.'];
        }

        return DB::transaction(function () use ($entry, $reason, $actor) {
            $locked = FuelEntry::lockForUpdate()->find($entry->id);
            if ($locked->status !== FuelEntry::STATUS_DRAFT) {
                return ['ok' => false, 'message' => 'Seul un brouillon peut être rejeté.'];
            }

            $locked->update([
                'status' => FuelEntry::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'validated_by' => $actor->id,
                'validated_at' => now(),
            ]);
            $this->logTransition($locked, 'rejected', FuelEntry::STATUS_DRAFT,
                FuelEntry::STATUS_REJECTED, $reason, $actor);

            return ['ok' => true, 'message' => "Entrée {$locked->reference} rejetée.", 'entry' => $locked->fresh()];
        });
    }

    /* ===================== SORTIES ===================== */

    /**
     * Stock disponible (litres) pour un type de carburant dans un magasin :
     * entrées validées − sorties validées, sur les lignes unite='L' dont
     * le produit mentionne le type de carburant.
     */
    public function availableFuelStock(int $warehouseId, string $fuelType, ?string $label = null): float
    {
        $produit = $fuelType === 'autre' && $label ? $label : $fuelType;

        $q = StockMovement::where('warehouse_id', $warehouseId)
            ->where('unite', 'L')
            ->where('status', 'validated')
            ->where('produit', 'like', '%' . $produit . '%');

        $in = (float) (clone $q)->whereIn('type', ['in', 'entry'])->sum('quantity');
        $out = (float) (clone $q)->whereIn('type', ['out', 'exit'])->sum('quantity');

        return round($in - $out, 2);
    }

    /** Créer un bon de sortie en brouillon. */
    public function createExit(array $data, User $actor, ?string $idempotencyKey = null): FuelExit
    {
        if ($idempotencyKey) {
            $existing = FuelExit::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $actor, $idempotencyKey) {
            $exit = FuelExit::create([
                'exit_number'        => $this->nextReference('SOR-CARB', 'fuel_exits', 'exit_number'),
                'operation_date'     => $data['operation_date'],
                'warehouse_id'       => $data['warehouse_id'],
                'fuel_type'          => $data['fuel_type'],
                'fuel_type_label'    => $data['fuel_type_label'] ?? null,
                'beneficiary_name'   => $data['beneficiary_name'],
                'beneficiary_id'     => $data['beneficiary_id'] ?? null,
                'vehicle_label'      => $data['vehicle_label'] ?? null,
                'vehicle_plate'      => $data['vehicle_plate'] ?? null,
                'destination'        => $data['destination'] ?? null,
                'purpose'            => $data['purpose'],
                'mission_number'     => $data['mission_number'] ?? null,
                'mission_start_date' => $data['mission_start_date'] ?? null,
                'mission_end_date'   => $data['mission_end_date'] ?? null,
                'authorization_ref'  => $data['authorization_ref'] ?? null,
                'quantity_requested' => round((float) $data['quantity_requested'], 2),
                'odometer_start'     => $data['odometer_start'] ?? null,
                'odometer_end'       => $data['odometer_end'] ?? null,
                'delivered_by_name'  => $data['delivered_by_name'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'status'             => FuelExit::STATUS_DRAFT,
                'created_by'         => $actor->id,
                'idempotency_key'    => $idempotencyKey,
            ]);

            $this->logTransition($exit, 'created', null, FuelExit::STATUS_DRAFT, null, $actor);

            return $exit;
        });
    }

    /**
     * Autoriser une sortie : fixe le plafond de litres et vérifie le
     * stock disponible. Ne débite PAS encore le stock.
     */
    public function authorizeExit(FuelExit $exit, float $quantityAuthorized, User $actor): array
    {
        if ($quantityAuthorized <= 0) {
            return ['ok' => false, 'message' => 'La quantité autorisée doit être positive.'];
        }

        return DB::transaction(function () use ($exit, $quantityAuthorized, $actor) {
            $locked = FuelExit::lockForUpdate()->find($exit->id);
            if ($locked->status === FuelExit::STATUS_AUTHORIZED) {
                return ['ok' => true, 'message' => "Sortie {$locked->exit_number} déjà autorisée.", 'exit' => $locked];
            }
            if ($locked->status !== FuelExit::STATUS_DRAFT) {
                return ['ok' => false, 'message' => "Une sortie {$locked->status} ne peut pas être autorisée."];
            }

            $available = $this->availableFuelStock(
                $locked->warehouse_id, $locked->fuel_type, $locked->fuel_type_label
            );
            if ($quantityAuthorized > $available) {
                return ['ok' => false, 'message' => sprintf(
                    'Stock insuffisant : %.2f L disponibles, %.2f L demandés.',
                    $available, $quantityAuthorized
                )];
            }

            $locked->update([
                'quantity_authorized' => round($quantityAuthorized, 2),
                'status'              => FuelExit::STATUS_AUTHORIZED,
                'authorized_by'       => $actor->id,
                'authorized_at'       => now(),
            ]);
            $this->logTransition($locked, 'authorized', FuelExit::STATUS_DRAFT,
                FuelExit::STATUS_AUTHORIZED, null, $actor);

            return ['ok' => true, 'message' => "Sortie {$locked->exit_number} autorisée ({$quantityAuthorized} L).", 'exit' => $locked->fresh()];
        });
    }

    /**
     * Valider la sortie effective : débite le stock des litres réellement
     * délivrés. Jamais au-delà de l'autorisation ni du stock disponible.
     */
    public function validateExit(FuelExit $exit, float $quantityDelivered, User $actor): array
    {
        if ($quantityDelivered <= 0) {
            return ['ok' => false, 'message' => 'La quantité délivrée doit être positive.'];
        }

        return DB::transaction(function () use ($exit, $quantityDelivered, $actor) {
            $locked = FuelExit::lockForUpdate()->find($exit->id);
            if ($locked->status === FuelExit::STATUS_VALIDATED) {
                return ['ok' => true, 'message' => "Sortie {$locked->exit_number} déjà validée.", 'exit' => $locked];
            }
            if ($locked->status !== FuelExit::STATUS_AUTHORIZED) {
                return ['ok' => false, 'message' => 'La sortie doit être autorisée avant livraison.'];
            }

            $delivered = round($quantityDelivered, 2);
            if ($locked->quantity_authorized !== null && $delivered > (float) $locked->quantity_authorized) {
                return ['ok' => false, 'message' => sprintf(
                    'Quantité délivrée (%.2f L) supérieure à l\'autorisation (%.2f L).',
                    $delivered, (float) $locked->quantity_authorized
                )];
            }

            $available = $this->availableFuelStock(
                $locked->warehouse_id, $locked->fuel_type, $locked->fuel_type_label
            );
            if ($delivered > $available) {
                return ['ok' => false, 'message' => sprintf(
                    'Stock insuffisant : %.2f L disponibles, %.2f L à délivrer.',
                    $available, $delivered
                )];
            }

            $movement = StockMovement::create([
                'warehouse_id' => $locked->warehouse_id,
                'type'         => 'exit',
                'produit'      => 'Carburant — ' . $locked->fuelLabel(),
                'unite'        => 'L',
                'quantity'     => $delivered,
                'reference'    => $locked->exit_number,
                'destinataire' => $locked->beneficiary_name,
                'numero_bon'   => $locked->exit_number,
                'date_mouvement' => $locked->operation_date,
                'reason'       => 'Dotation carburant : ' . $locked->purpose,
                'created_by'   => $actor->id,
                'status'       => 'validated',
                'validated_by' => $actor->id,
                'validated_at' => now(),
                'metadata'     => json_encode([
                    'fuel_exit_id'  => $locked->id,
                    'vehicle_plate' => $locked->vehicle_plate,
                    'mission'       => $locked->mission_number,
                ]),
            ]);

            $locked->update([
                'quantity_delivered' => $delivered,
                'status'             => FuelExit::STATUS_VALIDATED,
                'validated_by'       => $actor->id,
                'validated_at'       => now(),
                'stock_movement_id'  => $movement->id,
            ]);
            $this->logTransition($locked, 'validated', FuelExit::STATUS_AUTHORIZED,
                FuelExit::STATUS_VALIDATED, null, $actor);

            return ['ok' => true, 'message' => "Sortie {$locked->exit_number} validée — {$delivered} L débités du stock.", 'exit' => $locked->fresh()];
        });
    }

    /** Rejeter une sortie (draft ou authorized) avec motif. */
    public function rejectExit(FuelExit $exit, string $reason, User $actor): array
    {
        return DB::transaction(function () use ($exit, $reason, $actor) {
            $locked = FuelExit::lockForUpdate()->find($exit->id);
            if (!in_array($locked->status, [FuelExit::STATUS_DRAFT, FuelExit::STATUS_AUTHORIZED], true)) {
                return ['ok' => false, 'message' => "Une sortie {$locked->status} ne peut pas être rejetée."];
            }

            $locked->update([
                'status' => FuelExit::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'validated_by' => $actor->id,
                'validated_at' => now(),
            ]);
            $this->logTransition($locked, 'rejected', $locked->getOriginal('status'),
                FuelExit::STATUS_REJECTED, $reason, $actor);

            return ['ok' => true, 'message' => "Sortie {$locked->exit_number} rejetée.", 'exit' => $locked->fresh()];
        });
    }

    /* ===================== REMBOURSEMENTS ===================== */

    /**
     * Créer une demande de remboursement.
     * Détection de doublons : même agent + même référence de reçu, ou
     * même agent + même date + même montant (avertissement persisté,
     * jamais bloquant — une opération légitime peut ressembler à une autre).
     */
    public function createReimbursement(array $data, User $actor, ?string $idempotencyKey = null): FuelReimbursement
    {
        if ($idempotencyKey) {
            $existing = FuelReimbursement::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($data, $actor, $idempotencyKey) {
            $duplicateRef = $this->findDuplicateReimbursementRef($data);

            $reimb = FuelReimbursement::create([
                'reference'        => $this->nextReference('REM-CARB', 'fuel_reimbursements', 'reference'),
                'agent_name'       => $data['agent_name'],
                'agent_id'         => $data['agent_id'] ?? null,
                'service'          => $data['service'] ?? null,
                'expense_date'     => $data['expense_date'],
                'purpose'          => $data['purpose'],
                'activity_ref'     => $data['activity_ref'] ?? null,
                'activity_type'    => $data['activity_type'] ?? null,
                'vehicle_label'    => $data['vehicle_label'] ?? null,
                'vehicle_plate'    => $data['vehicle_plate'] ?? null,
                'amount_claimed'   => round((float) $data['amount_claimed'], 2),
                'quantity_liters'  => isset($data['quantity_liters']) ? round((float) $data['quantity_liters'], 2) : null,
                'receipt_ref'      => $data['receipt_ref'] ?? null,
                'amount_requested' => round((float) $data['amount_requested'], 2),
                'advance_amount'   => isset($data['advance_amount']) ? round((float) $data['advance_amount'], 2) : null,
                'status'           => FuelReimbursement::STATUS_DRAFT,
                'created_by'       => $actor->id,
                'duplicate_of_ref' => $duplicateRef,
                'idempotency_key'  => $idempotencyKey,
            ]);

            $this->logTransition($reimb, 'created', null, FuelReimbursement::STATUS_DRAFT,
                $duplicateRef ? "Doublon potentiel avec {$duplicateRef}" : null, $actor);

            return $reimb;
        });
    }

    /** Référence d'une demande antérieure ressemblante, ou null. */
    public function findDuplicateReimbursementRef(array $data): ?string
    {
        $query = FuelReimbursement::where('agent_name', $data['agent_name'])
            ->whereNotIn('status', [FuelReimbursement::STATUS_REJECTED]);

        if (!empty($data['receipt_ref'])) {
            $byReceipt = (clone $query)->where('receipt_ref', $data['receipt_ref'])->value('reference');
            if ($byReceipt) {
                return $byReceipt;
            }
        }

        return (clone $query)
            ->whereDate('expense_date', $data['expense_date'])
            ->where('amount_claimed', round((float) $data['amount_claimed'], 2))
            ->value('reference');
    }

    /** draft → submitted */
    public function submitReimbursement(FuelReimbursement $reimb, User $actor): array
    {
        return $this->transitionReimbursement($reimb, FuelReimbursement::STATUS_SUBMITTED,
            'submitted', [FuelReimbursement::STATUS_DRAFT], $actor);
    }

    /** submitted → in_review (le vérificateur prend le dossier) */
    public function reviewReimbursement(FuelReimbursement $reimb, ?string $comment, User $actor): array
    {
        $result = $this->transitionReimbursement($reimb, FuelReimbursement::STATUS_IN_REVIEW,
            'in_review', [FuelReimbursement::STATUS_SUBMITTED], $actor, $comment);

        if ($result['ok']) {
            $reimb->update(['reviewed_by' => $actor->id, 'review_comment' => $comment]);
        }

        return $result;
    }

    /**
     * in_review|submitted → approved.
     * Contrôle du rôle délégué au contrôleur (approbation financière).
     */
    public function approveReimbursement(FuelReimbursement $reimb, float $amountApproved, User $actor): array
    {
        if ($amountApproved <= 0) {
            return ['ok' => false, 'message' => 'Le montant approuvé doit être positif.'];
        }
        if ($amountApproved > (float) $reimb->amount_requested) {
            return ['ok' => false, 'message' => 'Le montant approuvé dépasse le montant demandé.'];
        }

        $result = $this->transitionReimbursement($reimb, FuelReimbursement::STATUS_APPROVED,
            'approved', [FuelReimbursement::STATUS_SUBMITTED, FuelReimbursement::STATUS_IN_REVIEW],
            $actor);

        if ($result['ok']) {
            $reimb->update([
                'amount_approved' => round($amountApproved, 2),
                'justified_amount' => round($amountApproved, 2),
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
        }

        return $result;
    }

    /** submitted|in_review → rejected (motif obligatoire). */
    public function rejectReimbursement(FuelReimbursement $reimb, string $reason, User $actor): array
    {
        $result = $this->transitionReimbursement($reimb, FuelReimbursement::STATUS_REJECTED,
            'rejected', [FuelReimbursement::STATUS_DRAFT, FuelReimbursement::STATUS_SUBMITTED,
                         FuelReimbursement::STATUS_IN_REVIEW], $actor, $reason);

        if ($result['ok']) {
            $reimb->update(['rejection_reason' => $reason, 'reviewed_by' => $actor->id]);
        }

        return $result;
    }

    /**
     * approved → paid : exige référence et date de paiement — jamais de
     * paiement enregistré sans confirmation/preuve.
     */
    public function payReimbursement(FuelReimbursement $reimb, string $paymentRef, string $paymentDate, User $actor): array
    {
        return DB::transaction(function () use ($reimb, $paymentRef, $paymentDate, $actor) {
            $locked = FuelReimbursement::lockForUpdate()->find($reimb->id);
            if ($locked->status === FuelReimbursement::STATUS_PAID
                || $locked->status === FuelReimbursement::STATUS_CLOSED) {
                return ['ok' => true, 'message' => "Demande {$locked->reference} déjà payée.", 'reimbursement' => $locked];
            }
            if ($locked->status !== FuelReimbursement::STATUS_APPROVED) {
                return ['ok' => false, 'message' => 'Seule une demande approuvée peut être payée.'];
            }

            $paid = (float) ($locked->amount_approved ?? 0);
            $complement = null;
            if ($locked->advance_amount !== null) {
                // Complément = approuvé − avance (négatif = reliquat à restituer)
                $complement = round($paid - (float) $locked->advance_amount, 2);
            }

            $locked->update([
                'status'            => FuelReimbursement::STATUS_PAID,
                'amount_paid'       => $paid,
                'complement_amount' => $complement,
                'payment_ref'       => $paymentRef,
                'payment_date'      => $paymentDate,
                'paid_by'           => $actor->id,
                'paid_at'           => now(),
            ]);
            $this->logTransition($locked, 'paid', FuelReimbursement::STATUS_APPROVED,
                FuelReimbursement::STATUS_PAID, null, $actor);

            return ['ok' => true, 'message' => "Paiement de {$paid} FCFA enregistré ({$locked->reference}).", 'reimbursement' => $locked->fresh()];
        });
    }

    /** paid → closed : dossier soldé après régularisation. */
    public function closeReimbursement(FuelReimbursement $reimb, User $actor): array
    {
        return $this->transitionReimbursement($reimb, FuelReimbursement::STATUS_CLOSED,
            'closed', [FuelReimbursement::STATUS_PAID], $actor);
    }

    private function transitionReimbursement(
        FuelReimbursement $reimb, string $to, string $action, array $fromAllowed, User $actor, ?string $reason = null
    ): array {
        if ($reimb->status === $to) {
            return ['ok' => true, 'message' => "Demande {$reimb->reference} déjà à l'état {$to}.", 'reimbursement' => $reimb];
        }

        return DB::transaction(function () use ($reimb, $to, $action, $fromAllowed, $actor, $reason) {
            $locked = FuelReimbursement::lockForUpdate()->find($reimb->id);
            if (!in_array($locked->status, $fromAllowed, true)) {
                return ['ok' => false, 'message' => "Transition impossible depuis l'état {$locked->status}."];
            }

            $from = $locked->status;
            $locked->update(['status' => $to]);
            $this->logTransition($locked, $action, $from, $to, $reason, $actor);

            return ['ok' => true, 'message' => "Demande {$locked->reference} : {$from} → {$to}.", 'reimbursement' => $locked->fresh()];
        });
    }

    /* ===================== PIÈCES JOINTES ===================== */

    /**
     * Stocke un justificatif sur le disque sécurisé (hors public/).
     * @throws \InvalidArgumentException type/taille invalide
     */
    public function storeAttachment(Model $subject, UploadedFile $file, User $actor, string $kind = 'justificatif'): FuelAttachment
    {
        $mime = $file->getMimeType();
        if (!in_array($mime, FuelAttachment::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException("Format refusé ({$mime}) : PDF, JPG ou PNG uniquement.");
        }
        if ($file->getSize() > FuelAttachment::MAX_SIZE_KB * 1024) {
            throw new \InvalidArgumentException('Fichier trop volumineux (max ' . FuelAttachment::MAX_SIZE_KB . ' Ko).');
        }

        $contents = $file->get();
        $hash = hash('sha256', $contents);
        $path = 'fuel/' . strtolower(class_basename($subject)) . '/' . $subject->id
            . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk(FuelAttachment::DISK)->put($path, $contents);

        return FuelAttachment::create([
            'attachable_type' => $subject->getMorphClass(),
            'attachable_id'   => $subject->id,
            'original_name'   => mb_substr($file->getClientOriginalName(), 0, 255),
            'stored_path'     => $path,
            'mime_type'       => $mime,
            'size'            => $file->getSize(),
            'sha256'          => $hash,
            'kind'            => $kind,
            'uploaded_by'     => $actor->id,
        ]);
    }

    /** Supprime une pièce d'un dossier non encore validé (éditable). */
    public function deleteAttachment(FuelAttachment $attachment, User $actor): array
    {
        $subject = $attachment->attachable;
        $editable = match (true) {
            $subject instanceof FuelEntry => $subject->status === FuelEntry::STATUS_DRAFT,
            $subject instanceof FuelExit  => in_array($subject->status, [FuelExit::STATUS_DRAFT, FuelExit::STATUS_AUTHORIZED], true),
            $subject instanceof FuelReimbursement => in_array($subject->status, [
                FuelReimbursement::STATUS_DRAFT, FuelReimbursement::STATUS_SUBMITTED,
            ], true),
            default => false,
        };

        if (!$editable) {
            return ['ok' => false, 'message' => 'Le dossier est figé : la pièce ne peut pas être supprimée.'];
        }

        Storage::disk(FuelAttachment::DISK)->delete($attachment->stored_path);
        $attachment->delete();

        return ['ok' => true, 'message' => 'Pièce supprimée.'];
    }

    /* ===================== TABLEAU DE BORD / AGRÉGATS ===================== */

    /**
     * Soldes de stock carburant : règle officielle
     *   disponible = entrées validées − sorties validées (unite 'L').
     * Regroupé par produit (type de carburant) et magasin.
     */
    public function fuelStockSummary()
    {
        $rows = StockMovement::selectRaw(
                "COALESCE(NULLIF(produit,''),'Carburant') as produit,
                 warehouse_id,
                 SUM(CASE WHEN type IN ('in','entry') THEN quantity ELSE 0 END) as total_in,
                 SUM(CASE WHEN type IN ('out','exit') THEN quantity ELSE 0 END) as total_out"
            )
            ->where('unite', 'L')
            ->where('status', 'validated')
            ->groupBy('produit', 'warehouse_id')
            ->get();

        $warehouses = Warehouse::whereIn('id', $rows->pluck('warehouse_id')->filter()->unique())
            ->pluck('name', 'id');

        return $rows->map(fn ($r) => [
            'produit' => $r->produit,
            'warehouse_id' => $r->warehouse_id,
            'warehouse_name' => $warehouses[$r->warehouse_id] ?? null,
            'total_in' => (float) $r->total_in,
            'total_out' => (float) $r->total_out,
            'balance' => round((float) $r->total_in - (float) $r->total_out, 2),
        ])->values();
    }

    /** Valeur du stock (méthode : coût moyen des entrées validées). */
    public function fuelStockValue(): array
    {
        $rows = StockMovement::selectRaw(
                "produit, warehouse_id,
                 SUM(CASE WHEN type IN ('in','entry') THEN quantity ELSE 0 END) as qty_in,
                 SUM(CASE WHEN type IN ('in','entry') THEN total ELSE 0 END) as amount_in,
                 SUM(CASE WHEN type IN ('out','exit') THEN quantity ELSE 0 END) as qty_out"
            )
            ->where('unite', 'L')
            ->where('status', 'validated')
            ->groupBy('produit', 'warehouse_id')
            ->get();

        return $rows->map(function ($r) {
            $qtyIn = (float) $r->qty_in;
            $avgCost = $qtyIn > 0 ? (float) $r->amount_in / $qtyIn : 0.0;
            $balance = $qtyIn - (float) $r->qty_out;

            return [
                'produit' => $r->produit,
                'warehouse_id' => $r->warehouse_id,
                'balance' => round($balance, 2),
                'avg_unit_cost' => round($avgCost, 2),
                'stock_value' => round($balance * $avgCost, 2),
            ];
        })->values()->all();
    }

    /** Compteurs d'opérations en attente par flux. */
    public function pendingCounts(): array
    {
        return [
            'entries'        => FuelEntry::where('status', FuelEntry::STATUS_DRAFT)->count(),
            'exits_draft'    => FuelExit::where('status', FuelExit::STATUS_DRAFT)->count(),
            'exits_authorized' => FuelExit::where('status', FuelExit::STATUS_AUTHORIZED)->count(),
            'reimbursements' => FuelReimbursement::whereIn('status', [
                FuelReimbursement::STATUS_SUBMITTED, FuelReimbursement::STATUS_IN_REVIEW,
            ])->count(),
        ];
    }

    /* ===================== JOURNAL / AUDIT ===================== */

    /** Transition tracée : fuel_status_logs + audit_logs. */
    private function logTransition(Model $subject, string $action, ?string $from, string $to, ?string $reason, User $actor): void
    {
        FuelStatusLog::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id'   => $subject->id,
            'action'       => $action,
            'from_status'  => $from,
            'to_status'    => $to,
            'reason'       => $reason,
            'actor_id'     => $actor->id,
        ]);

        try {
            AuditLog::create([
                'action'      => 'fuel.' . $action,
                'level'       => 'info',
                'status'      => 'success',
                'model_type'  => $subject->getMorphClass(),
                'model_id'    => $subject->id,
                'user_id'     => $actor->id,
                'description' => sprintf(
                    'Carburant %s : %s → %s par %s%s',
                    class_basename($subject), $from ?? '—', $to, $actor->name,
                    $reason ? " (motif : {$reason})" : ''
                ),
                'data' => json_encode([
                    'action' => $action, 'from' => $from, 'to' => $to, 'reason' => $reason,
                ]),
            ]);
        } catch (\Throwable $e) {
            // L'audit applicatif ne doit jamais bloquer le métier ;
            // fuel_status_logs (table dédiée) reste la piste primaire.
            \Illuminate\Support\Facades\Log::warning('AuditLog carburant : ' . $e->getMessage());
        }
    }
}
