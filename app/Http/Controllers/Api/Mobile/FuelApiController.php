<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FuelAttachment;
use App\Models\FuelEntry;
use App\Models\FuelExit;
use App\Models\FuelReimbursement;
use App\Services\Comptable\FuelService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API mobile carburant — « CSAR Comptable des matières » (Sanctum).
 * Miroir JSON du portail web /comptable/carburant : toute la logique
 * métier est dans FuelService (source unique partagée Web/API).
 *
 * Même règle financière que le portail web : approbation et paiement
 * des remboursements réservés au rôle admin (provisoire).
 */
class FuelApiController extends Controller
{
    public function __construct(private readonly FuelService $fuel)
    {
    }

    /* ===================== TABLEAU DE BORD ===================== */

    /** Soldes carburant + valorisation + dossiers en attente. */
    public function dashboard(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        return response()->json([
            'stock' => $this->fuel->fuelStockSummary(),
            'stock_value' => $this->fuel->fuelStockValue(),
            'pending' => $this->fuel->pendingCounts(),
        ]);
    }

    /* ===================== ENTRÉES ===================== */

    public function entriesIndex(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $query = FuelEntry::with('warehouse:id,name', 'creator:id,name')->orderByDesc('id');
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $s = '%' . $request->q . '%';
            $q->where(fn ($sub) => $sub
                ->where('reference', 'like', $s)
                ->orWhere('supplier', 'like', $s)
                ->orWhere('invoice_number', 'like', $s));
        });

        return response()->json($query->paginate(30)->withQueryString());
    }

    public function entriesStore(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $data = $request->validate([
            'operation_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier' => 'required|string|max:255',
            'fuel_type' => 'required|in:essence,gasoil,petrole,autre',
            'fuel_type_label' => 'nullable|string|max:255|required_if:fuel_type,autre',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0.01',
            'invoice_number' => 'nullable|string|max:100',
            'invoice_date' => 'nullable|date',
            'delivery_note_ref' => 'nullable|string|max:100',
            'funding_source' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $entry = $this->fuel->createEntry($data, $request->user(), $data['idempotency_key'] ?? null);

        return response()->json([
            'message' => "Entrée {$entry->reference} créée en brouillon.",
            'entry' => $entry->load('warehouse:id,name'),
        ], 201);
    }

    public function entriesShow(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $entry = FuelEntry::with(
            'warehouse:id,name', 'creator:id,name', 'validator:id,name',
            'attachments.uploader:id,name', 'statusLogs.actor:id,name'
        )->findOrFail($id);

        return response()->json(['entry' => $entry]);
    }

    public function entriesValidate(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $result = $this->fuel->validateEntry(FuelEntry::findOrFail($id), $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'entry' => FuelEntry::findOrFail($id),
        ]);
    }

    public function entriesReject(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectEntry(FuelEntry::findOrFail($id), $request->reason, $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'entry' => FuelEntry::findOrFail($id),
        ]);
    }

    /* ===================== SORTIES ===================== */

    public function exitsIndex(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $query = FuelExit::with('warehouse:id,name', 'creator:id,name')->orderByDesc('id');
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $s = '%' . $request->q . '%';
            $q->where(fn ($sub) => $sub
                ->where('exit_number', 'like', $s)
                ->orWhere('beneficiary_name', 'like', $s)
                ->orWhere('vehicle_plate', 'like', $s)
                ->orWhere('mission_number', 'like', $s));
        });

        return response()->json($query->paginate(30)->withQueryString());
    }

    public function exitsStore(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $data = $request->validate([
            'operation_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type' => 'required|in:essence,gasoil,petrole,autre',
            'fuel_type_label' => 'nullable|string|max:255|required_if:fuel_type,autre',
            'beneficiary_name' => 'required|string|max:255',
            'vehicle_label' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:50',
            'destination' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:255',
            'mission_number' => 'nullable|string|max:100',
            'mission_start_date' => 'nullable|date',
            'mission_end_date' => 'nullable|date|after_or_equal:mission_start_date',
            'authorization_ref' => 'nullable|string|max:100',
            'quantity_requested' => 'required|numeric|min:0.01',
            'odometer_start' => 'nullable|integer|min:0',
            'odometer_end' => 'nullable|integer|min:0|gte:odometer_start',
            'delivered_by_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $exit = $this->fuel->createExit($data, $request->user(), $data['idempotency_key'] ?? null);

        return response()->json([
            'message' => "Sortie {$exit->exit_number} créée en brouillon.",
            'exit' => $exit->load('warehouse:id,name'),
        ], 201);
    }

    public function exitsShow(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $exit = FuelExit::with(
            'warehouse:id,name', 'creator:id,name', 'authorizer:id,name', 'validator:id,name',
            'attachments.uploader:id,name', 'statusLogs.actor:id,name'
        )->findOrFail($id);

        return response()->json([
            'exit' => $exit,
            'available_liters' => $this->fuel->availableFuelStock(
                $exit->warehouse_id, $exit->fuel_type, $exit->fuel_type_label
            ),
        ]);
    }

    public function exitsAuthorize(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['quantity_authorized' => 'required|numeric|min:0.01']);
        $result = $this->fuel->authorizeExit(
            FuelExit::findOrFail($id), (float) $request->quantity_authorized, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'exit' => FuelExit::findOrFail($id),
        ]);
    }

    public function exitsValidate(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['quantity_delivered' => 'required|numeric|min:0.01']);
        $result = $this->fuel->validateExit(
            FuelExit::findOrFail($id), (float) $request->quantity_delivered, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'exit' => FuelExit::findOrFail($id),
        ]);
    }

    public function exitsReject(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectExit(FuelExit::findOrFail($id), $request->reason, $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'exit' => FuelExit::findOrFail($id),
        ]);
    }

    /* ===================== REMBOURSEMENTS ===================== */

    public function reimbsIndex(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $query = FuelReimbursement::with('creator:id,name')->orderByDesc('id');
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('q'), function ($q) use ($request) {
            $s = '%' . $request->q . '%';
            $q->where(fn ($sub) => $sub
                ->where('reference', 'like', $s)
                ->orWhere('agent_name', 'like', $s)
                ->orWhere('receipt_ref', 'like', $s)
                ->orWhere('vehicle_plate', 'like', $s));
        });

        return response()->json($query->paginate(30)->withQueryString());
    }

    public function reimbsStore(Request $request)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $data = $request->validate([
            'agent_name' => 'required|string|max:255',
            'service' => 'nullable|string|max:255',
            'expense_date' => 'required|date',
            'purpose' => 'required|string|max:255',
            'activity_ref' => 'nullable|string|max:100',
            'activity_type' => 'nullable|in:mission,atelier,activite',
            'vehicle_label' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:50',
            'amount_claimed' => 'required|numeric|min:0.01',
            'quantity_liters' => 'nullable|numeric|min:0',
            'receipt_ref' => 'nullable|string|max:100',
            'amount_requested' => 'required|numeric|min:0.01|lte:amount_claimed',
            'advance_amount' => 'nullable|numeric|min:0',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $reimb = $this->fuel->createReimbursement($data, $request->user(), $data['idempotency_key'] ?? null);

        $message = "Demande {$reimb->reference} créée.";
        if ($reimb->duplicate_of_ref) {
            $message .= " Attention : doublon potentiel avec {$reimb->duplicate_of_ref}.";
        }

        return response()->json([
            'message' => $message,
            'reimbursement' => $reimb,
        ], 201);
    }

    public function reimbsShow(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $reimb = FuelReimbursement::with(
            'creator:id,name', 'reviewer:id,name', 'approver:id,name', 'payer:id,name',
            'attachments.uploader:id,name', 'statusLogs.actor:id,name'
        )->findOrFail($id);

        return response()->json([
            'reimbursement' => $reimb,
            'regularization_state' => $reimb->regularizationState(),
            'can_decide' => $request->user()->role === 'admin',
        ]);
    }

    public function reimbsSubmit(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $result = $this->fuel->submitReimbursement(FuelReimbursement::findOrFail($id), $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    public function reimbsReview(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['comment' => 'nullable|string|max:500']);
        $result = $this->fuel->reviewReimbursement(
            FuelReimbursement::findOrFail($id), $request->comment, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    public function reimbsApprove(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => "L'approbation financière est réservée au rôle admin."], 403);
        }

        $request->validate(['amount_approved' => 'required|numeric|min:0.01']);
        $result = $this->fuel->approveReimbursement(
            FuelReimbursement::findOrFail($id), (float) $request->amount_approved, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    public function reimbsReject(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectReimbursement(
            FuelReimbursement::findOrFail($id), $request->reason, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    public function reimbsPay(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => "L'enregistrement du paiement est réservé au rôle admin."], 403);
        }

        $request->validate([
            'payment_ref' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);
        $result = $this->fuel->payReimbursement(
            FuelReimbursement::findOrFail($id), $request->payment_ref, $request->payment_date, $request->user()
        );
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    public function reimbsClose(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $result = $this->fuel->closeReimbursement(FuelReimbursement::findOrFail($id), $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'reimbursement' => FuelReimbursement::findOrFail($id),
        ]);
    }

    /* ===================== PIÈCES JOINTES ===================== */

    /** Upload multipart : champ 'file' (pdf/jpg/png, ≤ 8 Mo), 'kind' optionnel. */
    public function attachmentStore(Request $request, string $type, int $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $request->validate([
            'file' => 'required|file|max:8192|mimes:pdf,jpg,jpeg,png',
            'kind' => 'nullable|in:facture,bon_livraison,recu,ordre_mission,justificatif',
        ]);

        $subject = $this->resolveFuelSubject($type, $id);

        try {
            $attachment = $this->fuel->storeAttachment(
                $subject, $request->file('file'), $request->user(), $request->kind ?: 'justificatif'
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "Pièce « {$attachment->original_name} » ajoutée.",
            'attachment' => $attachment,
        ], 201);
    }

    /** Téléchargement contrôlé : le fichier n'est jamais public. */
    public function attachmentDownload(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $attachment = FuelAttachment::findOrFail($id);
        $disk = Storage::disk(FuelAttachment::DISK);

        if (!$disk->exists($attachment->stored_path)) {
            return response()->json(['message' => 'Fichier introuvable.'], 404);
        }

        return $disk->download($attachment->stored_path, $attachment->original_name);
    }

    public function attachmentDelete(Request $request, $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        $result = $this->fuel->deleteAttachment(FuelAttachment::findOrFail($id), $request->user());
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message']]);
    }

    /* ===================== PDF ===================== */

    /** PDF du document (entry|exit|reimb) via dompdf. */
    public function pdf(Request $request, string $type, int $id)
    {
        if ($resp = $this->denyNonComptable($request)) {
            return $resp;
        }

        [$model, $view, $label] = match ($type) {
            'entry' => [
                FuelEntry::with('warehouse', 'creator', 'validator', 'attachments')->findOrFail($id),
                'comptable.carburant.pdf.entry',
                'Entree-carburant',
            ],
            'exit' => [
                FuelExit::with('warehouse', 'creator', 'authorizer', 'validator', 'attachments')->findOrFail($id),
                'comptable.carburant.pdf.exit',
                'Bon-sortie-carburant',
            ],
            'reimb' => [
                FuelReimbursement::with('creator', 'reviewer', 'approver', 'payer', 'attachments')->findOrFail($id),
                'comptable.carburant.pdf.reimb',
                'Demande-remboursement',
            ],
            default => abort(404),
        };

        $ref = $model->reference ?? $model->exit_number;
        return \Barryvdh\DomPDF\Facade\Pdf::loadView($view, ['item' => $model])
            ->setPaper('a4')
            ->download("{$label}-{$ref}.pdf");
    }

    /* ===================== HELPERS ===================== */

    /** Résout type ('entry'|'exit'|'reimb') + id vers le modèle Fuel*. */
    private function resolveFuelSubject(string $type, int $id): Model
    {
        return match ($type) {
            'entry' => FuelEntry::findOrFail($id),
            'exit' => FuelExit::findOrFail($id),
            'reimb' => FuelReimbursement::findOrFail($id),
            default => abort(404),
        };
    }

    /** Vérification du rôle à chaque requête protégée (même règle que l'API comptable). */
    private function denyNonComptable(Request $request)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['comptable', 'admin'], true) || !$user->is_active) {
            return response()->json(['message' => 'Accès réservé au Comptable des matières.'], 403);
        }
        return null;
    }
}
