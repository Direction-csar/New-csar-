<?php

namespace App\Services\Magasin;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Circuit opérationnel DG → DTL → Inspecteur → Magasinier → vérification.
 *
 * Règle d'or : un mouvement créé dans le cadre d'une opération reste
 * 'en_attente' et NE compte PAS dans le stock officiel tant que
 * l'inspecteur ne l'a pas vérifié ('verifie').
 */
class StockOperationService
{
    /** Statuts de mouvement exclus du stock officiel. */
    public const MOVEMENT_STATUTS_HORS_STOCK = ['en_attente', 'rejetee', 'anomalie', 'rejected'];

    public function __construct(private WarehouseStockService $stock) {}

    // ------------------------------------------------------------------
    // Étape 1 — DG : création de l'instruction
    // ------------------------------------------------------------------
    public function create(User $author, array $data): array
    {
        $type = $data['type'] ?? null;
        $warehouseId = (int) ($data['warehouse_id'] ?? 0);

        if (!in_array($type, StockOperation::TYPES, true)) {
            return ['ok' => false, 'message' => 'Type d\'opération invalide.', 'status' => 422];
        }
        if (!Warehouse::where('id', $warehouseId)->exists()) {
            return ['ok' => false, 'message' => 'Magasin introuvable.', 'status' => 422];
        }
        if ($type === StockOperation::TYPE_TRANSFER) {
            $destId = (int) ($data['destination_warehouse_id'] ?? 0);
            if ($destId === $warehouseId || !Warehouse::where('id', $destId)->exists()) {
                return ['ok' => false, 'message' => 'Magasin de destination invalide pour un transfert.', 'status' => 422];
            }
        } else {
            $data['destination_warehouse_id'] = null;
        }
        if (($data['quantity_requested'] ?? 0) <= 0) {
            return ['ok' => false, 'message' => 'La quantité demandée doit être positive.', 'status' => 422];
        }

        $op = StockOperation::create([
            'reference' => $this->nextReference(),
            'type' => $type,
            'warehouse_id' => $warehouseId,
            'destination_warehouse_id' => $data['destination_warehouse_id'] ?? null,
            'produit' => $data['produit'] ?? null,
            'unite' => $data['unite'] ?? null,
            'format' => $data['format'] ?? null,
            'quantity_requested' => $data['quantity_requested'],
            'quantity_requested_kg' => $data['quantity_requested_kg'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'status' => StockOperation::STATUS_SOUMISE,
            'created_by' => $author->id,
        ]);

        $this->audit($author, 'stock_operation.create', $op, 'Instruction de stock ' . $op->reference . ' créée par la DG');
        $this->notifyRole('dtl', 'Nouvelle instruction de stock',
            "{$op->reference} — {$op->type_label} sur {$op->warehouse?->name}. En attente d'affectation.",
            ['stock_operation_id' => $op->id]);

        return ['ok' => true, 'operation' => $op];
    }

    // ------------------------------------------------------------------
    // Étape 2 — DTL : affectation à un inspecteur
    // ------------------------------------------------------------------
    public function assignInspector(User $dtl, int $operationId, int $inspectorId, ?string $comment = null): array
    {
        $op = StockOperation::findOrFail($operationId);
        if ($op->status !== StockOperation::STATUS_SOUMISE) {
            return ['ok' => false, 'message' => 'Seule une opération « soumise » peut être affectée.', 'status' => 409];
        }
        $inspector = User::findOrFail($inspectorId);
        if ($inspector->role !== 'inspecteur') {
            return ['ok' => false, 'message' => 'L\'opération doit être affectée à un inspecteur.', 'status' => 422];
        }

        $op->update([
            'status' => StockOperation::STATUS_AFFECTEE,
            'assigned_inspector_id' => $inspector->id,
            'dtl_assigned_by' => $dtl->id,
            'dtl_assigned_at' => now(),
            'dtl_comment' => $comment ?? $op->dtl_comment,
        ]);

        $this->audit($dtl, 'stock_operation.assign_inspector', $op,
            "Inspecteur {$inspector->name} affecté sur {$op->reference}");
        $this->notify($inspector, 'Inspection de stock à mener',
            "{$op->reference} — {$op->type_label} sur {$op->warehouse?->name} vous est affectée.",
            ['stock_operation_id' => $op->id]);

        return ['ok' => true, 'operation' => $op->fresh()];
    }

    // ------------------------------------------------------------------
    // Étape 3 — Inspecteur : transmission des consignes au magasinier
    // ------------------------------------------------------------------
    public function transmitToMagasinier(User $inspector, int $operationId, int $magasinierId, ?string $comment = null): array
    {
        $op = StockOperation::findOrFail($operationId);
        if ($op->status !== StockOperation::STATUS_AFFECTEE || $op->assigned_inspector_id !== $inspector->id) {
            return ['ok' => false, 'message' => 'Opération non affectée à cet inspecteur.', 'status' => 403];
        }
        $magasinier = User::findOrFail($magasinierId);
        if ($magasinier->role !== 'magasinier') {
            return ['ok' => false, 'message' => 'Le destinataire doit être un magasinier.', 'status' => 422];
        }
        // Le magasinier doit être rattaché au magasin concerné (ou à la destination pour un transfert)
        $warehouseIds = $op->type === StockOperation::TYPE_TRANSFER
            ? [$op->warehouse_id, $op->destination_warehouse_id]
            : [$op->warehouse_id];
        $attached = Warehouse::whereIn('id', $warehouseIds)
            ->whereHas('users', fn ($q) => $q->where('id', $magasinier->id))
            ->exists();
        if (!$attached) {
            return ['ok' => false, 'message' => 'Ce magasinier n\'est pas affecté au(x) magasin(s) concerné(s).', 'status' => 422];
        }

        $op->update([
            'status' => StockOperation::STATUS_TRANSMISE,
            'assigned_magasinier_id' => $magasinier->id,
            'inspector_transmitted_at' => now(),
            'inspector_comment' => $comment ?? $op->inspector_comment,
        ]);

        $this->audit($inspector, 'stock_operation.transmit', $op,
            "Consignes transmises à {$magasinier->name} pour {$op->reference}");
        $this->notify($magasinier, 'Opération de stock à exécuter',
            "{$op->reference} — {$op->type_label} sur {$op->warehouse?->name}. Veuillez enregistrer l'opération réelle.",
            ['stock_operation_id' => $op->id]);

        return ['ok' => true, 'operation' => $op->fresh()];
    }

    // ------------------------------------------------------------------
    // Étape 4 — Magasinier : enregistrement de l'opération réelle
    // Crée le(s) mouvement(s) avec status 'en_attente' → hors stock officiel.
    // ------------------------------------------------------------------
    public function execute(User $magasinier, int $operationId, array $data): array
    {
        $op = StockOperation::findOrFail($operationId);
        if ($op->status !== StockOperation::STATUS_TRANSMISE || $op->assigned_magasinier_id !== $magasinier->id) {
            return ['ok' => false, 'message' => 'Opération non transmise à cet utilisateur.', 'status' => 403];
        }

        $executedSacs = (float) ($data['quantity_executed'] ?? 0);
        $executedKg = (float) ($data['quantity_executed_kg'] ?? 0);
        if ($executedSacs <= 0) {
            return ['ok' => false, 'message' => 'La quantité réellement exécutée doit être positive.', 'status' => 422];
        }

        try {
            $result = DB::transaction(function () use ($op, $magasinier, $data, $executedSacs, $executedKg) {
                // Verrouille l'opération contre les doubles exécutions
                $op = StockOperation::whereKey($op->id)->lockForUpdate()->firstOrFail();
                if ($op->status !== StockOperation::STATUS_TRANSMISE) {
                    return ['error' => 'Opération déjà traitée.', 'status' => 409];
                }

                $movements = $this->createPendingMovements($op, $magasinier, $data, $executedSacs, $executedKg);
                if (isset($movements['error'])) {
                    return $movements;
                }

                $op->update([
                    'status' => StockOperation::STATUS_EXECUTEE,
                    'executed_by' => $magasinier->id,
                    'executed_at' => now(),
                    'quantity_executed' => $executedSacs,
                    'quantity_executed_kg' => $executedKg > 0 ? $executedKg : null,
                ]);

                return ['movements' => $movements, 'op' => $op];
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur exécution : ' . $e->getMessage(), 'status' => 500];
        }

        if (isset($result['error'])) {
            return ['ok' => false, 'message' => $result['error'], 'status' => $result['status'] ?? 422];
        }

        $this->audit($magasinier, 'stock_operation.execute', $result['op'],
            "Opération {$op->reference} exécutée : {$executedSacs} contenants");
        if ($op->assigned_inspector_id) {
            $inspector = User::find($op->assigned_inspector_id);
            if ($inspector) {
                $this->notify($inspector, 'Opération exécutée à vérifier',
                    "{$op->reference} a été exécutée par {$magasinier->name}. Vérification requise.",
                    ['stock_operation_id' => $op->id]);
            }
        }

        return ['ok' => true, 'operation' => $result['op']->fresh(), 'movements' => $result['movements']];
    }

    /**
     * Crée les mouvements 'en_attente' correspondant au type d'opération.
     * Ne met PAS à jour warehouses.current_stock (stock officiel).
     */
    private function createPendingMovements(StockOperation $op, User $magasinier, array $data, float $sacs, float $kg): array
    {
        $label = $this->operationLabel($op);
        $metaBase = [
            'stock_operation_id' => $op->id,
            'operation_reference' => $op->reference,
            'position' => $data['position'] ?? null,
            'observation' => $data['observation'] ?? null,
            'movement_date' => $data['movement_date'] ?? now()->toDateString(),
            'photo_base64' => $data['photo_base64'] ?? null,
            'signature_base64' => $data['signature_base64'] ?? null,
            'client_ref' => $data['client_ref'] ?? null,
        ];

        $balance = $this->stock->balance($op->warehouse_id);

        // Contrôle de cohérence AVANT exécution (stock officiel hors attentes)
        if (in_array($op->type, [StockOperation::TYPE_EXIT, StockOperation::TYPE_INVENTORY], true) && $sacs > $balance['sacs']) {
            return ['error' => 'Solde officiel insuffisant. Disponible : ' . $balance['sacs'] . ' contenants', 'status' => 422];
        }
        if ($manque = $this->stock->productShortage($op->warehouse_id, $label, $sacs)) {
            return ['error' => $manque, 'status' => 422];
        }

        $created = [];

        if ($op->type === StockOperation::TYPE_TRANSFER) {
            $destId = (int) $op->destination_warehouse_id;
            $destName = Warehouse::find($destId)?->name;
            $srcName = Warehouse::find($op->warehouse_id)?->name;
            $numero = 'T-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            $mOut = StockMovement::create([
                'warehouse_id' => $op->warehouse_id,
                'stock_id' => null,
                'type' => 'transfer',
                'quantity' => $sacs,
                'quantity_before' => $balance['sacs'],
                'quantity_after' => $balance['sacs'] - $sacs,
                'reason' => $label . ' (Transfert vers: ' . $destName . ')',
                'reference' => 'OP-' . $op->reference,
                'numero_transfert' => $numero,
                'entrepot_destination_id' => $destId,
                'created_by' => $magasinier->id,
                'stock_operation_id' => $op->id,
                'status' => 'en_attente',
            ]);
            $mOut->metadata = json_encode($metaBase + [
                'transfer_type' => 'out',
                'destination_warehouse_id' => $destId,
                'destination_warehouse_name' => $destName,
                'entry_sacs' => 0, 'entry_kg' => 0,
                'exit_sacs' => $sacs, 'exit_kg' => $kg,
                'numero_transfert' => $numero,
            ]);
            $mOut->save();
            $created[] = $mOut;

            $balDest = $this->stock->balance($destId);
            $mIn = StockMovement::create([
                'warehouse_id' => $destId,
                'stock_id' => null,
                'type' => 'entry',
                'quantity' => $sacs,
                'quantity_before' => $balDest['sacs'],
                'quantity_after' => $balDest['sacs'] + $sacs,
                'reason' => $label . ' (Transfert depuis: ' . $srcName . ')',
                'reference' => 'OP-' . $op->reference . '-IN',
                'numero_transfert' => $numero,
                'entrepot_destination_id' => $op->warehouse_id,
                'created_by' => $magasinier->id,
                'stock_operation_id' => $op->id,
                'status' => 'en_attente',
            ]);
            $mIn->metadata = json_encode($metaBase + [
                'transfer_type' => 'in',
                'source_warehouse_id' => $op->warehouse_id,
                'source_warehouse_name' => $srcName,
                'entry_sacs' => $sacs, 'entry_kg' => $kg,
                'exit_sacs' => 0, 'exit_kg' => 0,
                'numero_transfert' => $numero,
            ]);
            $mIn->save();
            $created[] = $mIn;
        } else {
            $isEntry = in_array($op->type, [StockOperation::TYPE_ENTRY], true);
            $type = $op->type === StockOperation::TYPE_INVENTORY ? 'adjustment' : ($isEntry ? 'entry' : 'exit');

            $m = StockMovement::create([
                'warehouse_id' => $op->warehouse_id,
                'stock_id' => null,
                'type' => $type,
                'quantity' => $op->type === StockOperation::TYPE_INVENTORY ? abs($sacs - $balance['sacs']) : $sacs,
                'quantity_before' => $balance['sacs'],
                'quantity_after' => $op->type === StockOperation::TYPE_INVENTORY ? $sacs : ($isEntry ? $balance['sacs'] + $sacs : $balance['sacs'] - $sacs),
                'reason' => $label . ($op->type === StockOperation::TYPE_INVENTORY ? ' (Inventaire)' : ''),
                'reference' => 'OP-' . $op->reference,
                'raison_ajustement' => $op->type === StockOperation::TYPE_INVENTORY ? 'Inventaire physique' : null,
                'created_by' => $magasinier->id,
                'stock_operation_id' => $op->id,
                'status' => 'en_attente',
            ]);
            $m->metadata = json_encode($metaBase + [
                'inventory_type' => $op->type === StockOperation::TYPE_INVENTORY,
                'entry_sacs' => $isEntry ? $sacs : 0,
                'entry_kg' => $isEntry ? $kg : 0,
                'exit_sacs' => $isEntry ? 0 : $sacs,
                'exit_kg' => $isEntry ? 0 : $kg,
            ]);
            $m->save();
            $created[] = $m;
        }

        return $created;
    }

    private function operationLabel(StockOperation $op): string
    {
        $parts = [];
        if ($op->produit) {
            $parts[] = $op->produit;
        }
        if ($op->format) {
            $parts[] = '[' . $op->format . ($op->unite === 'L' ? 'L' : 'kg') . ']';
        }
        $parts[] = '— ' . $op->type_label . ' ' . $op->reference;
        return implode(' ', $parts);
    }

    // ------------------------------------------------------------------
    // Étape 5 — Inspecteur : vérification / anomalie / rejet
    // ------------------------------------------------------------------
    public function verify(User $inspector, int $operationId, ?string $comment = null): array
    {
        $op = StockOperation::findOrFail($operationId);
        if (!in_array($op->status, [StockOperation::STATUS_EXECUTEE, StockOperation::STATUS_ANOMALIE], true)
            || $op->assigned_inspector_id !== $inspector->id) {
            return ['ok' => false, 'message' => 'Opération non vérifiable par cet inspecteur.', 'status' => 403];
        }

        // Transfert : la jambe « in » exige la confirmation de réception
        // du magasin destinataire avant vérification.
        $pendingIn = StockMovement::where('stock_operation_id', $op->id)
            ->where('status', 'en_attente')
            ->get()
            ->first(fn ($m) => $this->stock->isTransferIn($m)
                && empty((json_decode($m->metadata ?? '{}', true) ?: [])['receipt_confirmed_at']));
        if ($pendingIn) {
            return ['ok' => false, 'message' => 'En attente de la confirmation de réception par le magasin destinataire.', 'status' => 409];
        }

        try {
            DB::transaction(function () use ($op, $inspector, $comment) {
                $op = StockOperation::whereKey($op->id)->lockForUpdate()->firstOrFail();

                $movements = StockMovement::where('stock_operation_id', $op->id)
                    ->where('status', 'en_attente')
                    ->get();

                foreach ($movements as $m) {
                    $m->update([
                        'status' => 'verifie',
                        'validated_by' => $inspector->id,
                        'validated_at' => now(),
                    ]);
                }

                // Met à jour le stock officiel (current_stock = solde vérifié)
                $this->applyVerifiedMovements($movements);

                $op->update([
                    'status' => StockOperation::STATUS_VERIFIEE,
                    'verified_by' => $inspector->id,
                    'verified_at' => now(),
                    'inspector_comment' => $comment ?? $op->inspector_comment,
                ]);
            });
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Erreur vérification : ' . $e->getMessage(), 'status' => 500];
        }

        $this->audit($inspector, 'stock_operation.verify', $op, "Opération {$op->reference} vérifiée");
        $this->notify($op->executor, 'Opération vérifiée',
            "{$op->reference} a été vérifiée par {$inspector->name} et intégrée au stock officiel.",
            ['stock_operation_id' => $op->id]);

        return ['ok' => true, 'operation' => $op->fresh()];
    }

    public function reportAnomaly(User $inspector, int $operationId, string $report, ?array $attachments = null): array
    {
        $op = StockOperation::findOrFail($operationId);
        if (!in_array($op->status, [StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE], true)
            || $op->assigned_inspector_id !== $inspector->id) {
            return ['ok' => false, 'message' => 'Opération non accessible par cet inspecteur.', 'status' => 403];
        }

        $op->update([
            'status' => StockOperation::STATUS_ANOMALIE,
            'anomaly_report' => $report,
            'attachments' => $attachments ?? $op->attachments,
        ]);

        $this->audit($inspector, 'stock_operation.anomaly', $op, "Anomalie signalée sur {$op->reference} : {$report}");
        if ($op->assigned_magasinier_id) {
            $mag = User::find($op->assigned_magasinier_id);
            if ($mag) {
                $this->notify($mag, 'Anomalie signalée',
                    "Une anomalie a été signalée sur {$op->reference}. Contactez l'inspecteur.",
                    ['stock_operation_id' => $op->id]);
            }
        }

        return ['ok' => true, 'operation' => $op->fresh()];
    }

    public function reject(User $actor, int $operationId, string $reason): array
    {
        $op = StockOperation::findOrFail($operationId);
        $allowed = match ($actor->role) {
            'inspecteur' => $op->assigned_inspector_id === $actor->id
                && in_array($op->status, [StockOperation::STATUS_TRANSMISE, StockOperation::STATUS_EXECUTEE, StockOperation::STATUS_ANOMALIE], true),
            'dtl', 'dg', 'admin', 'super_admin' => in_array($op->status, [StockOperation::STATUS_SOUMISE, StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_ANOMALIE], true),
            default => false,
        };
        if (!$allowed) {
            return ['ok' => false, 'message' => 'Opération non rejetable dans son état actuel.', 'status' => 409];
        }

        DB::transaction(function () use ($op, $reason) {
            StockMovement::where('stock_operation_id', $op->id)
                ->whereIn('status', ['en_attente', 'anomalie'])
                ->update(['status' => 'rejetee']);

            $op->update([
                'status' => StockOperation::STATUS_REJETEE,
                'rejection_reason' => $reason,
            ]);
        });

        $this->audit($actor, 'stock_operation.reject', $op, "Opération {$op->reference} rejetée : {$reason}");
        return ['ok' => true, 'operation' => $op->fresh()];
    }

    /** Annulation par la DG — uniquement avant exécution. */
    public function cancel(User $actor, int $operationId, ?string $reason = null): array
    {
        $op = StockOperation::findOrFail($operationId);
        if (!in_array($actor->role, ['dg', 'admin', 'super_admin'], true)
            || !in_array($op->status, [StockOperation::STATUS_SOUMISE, StockOperation::STATUS_AFFECTEE, StockOperation::STATUS_TRANSMISE], true)) {
            return ['ok' => false, 'message' => 'Opération non annulable.', 'status' => 409];
        }

        $op->update([
            'status' => StockOperation::STATUS_ANNULEE,
            'rejection_reason' => $reason ?? 'Annulée par la direction',
        ]);

        $this->audit($actor, 'stock_operation.cancel', $op, "Opération {$op->reference} annulée");
        return ['ok' => true, 'operation' => $op->fresh()];
    }

    // ------------------------------------------------------------------
    // Internes
    // ------------------------------------------------------------------
    private function applyVerifiedMovements($movements): void
    {
        $byWarehouse = [];
        foreach ($movements as $m) {
            $delta = $this->stock->signedDelta($m);
            $byWarehouse[$m->warehouse_id] = ($byWarehouse[$m->warehouse_id] ?? 0) + $delta;
        }
        foreach ($byWarehouse as $warehouseId => $delta) {
            Warehouse::where('id', $warehouseId)->increment('current_stock', $delta);
        }
    }

    private function nextReference(): string
    {
        $year = now()->format('Y');
        $last = StockOperation::withTrashed()->where('reference', 'like', "OP-{$year}-%")
            ->orderByDesc('id')->value('reference');
        $seq = $last ? ((int) substr($last, -6)) + 1 : 1;
        return sprintf('OP-%s-%06d', $year, $seq);
    }

    private function audit(User $user, string $action, StockOperation $op, string $description): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'level' => 'info',
                'status' => 'success',
                'model_type' => StockOperation::class,
                'model_id' => $op->id,
                'description' => $description,
                'data' => ['reference' => $op->reference, 'status' => $op->status],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Audit stock_operation échoué : ' . $e->getMessage());
        }
    }

    private function notify(User $user, string $title, string $message, array $meta = []): void
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'stock_operation',
                'title' => $title,
                'message' => $message,
                'data' => $meta,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification stock_operation échouée : ' . $e->getMessage());
        }
    }

    private function notifyRole(string $role, string $title, string $message, array $meta = []): void
    {
        User::where('role', $role)->where('is_active', true)->each(
            fn (User $u) => $this->notify($u, $title, $message, $meta)
        );
    }
}
