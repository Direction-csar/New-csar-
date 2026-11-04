<?php

namespace App\Http\Controllers\Comptable;

use App\Http\Controllers\Controller;
use App\Models\FuelAttachment;
use App\Models\FuelEntry;
use App\Models\FuelExit;
use App\Models\FuelReimbursement;
use App\Models\Warehouse;
use App\Services\Comptable\FuelService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Portail web « Gestion du carburant » — Comptable des matières.
 * Toute la logique métier est dans FuelService (partagé avec l'API mobile).
 *
 * Règle d'autorisation financière : l'approbation et le paiement des
 * remboursements sont réservés au rôle admin tant que le rôle métier
 * habilité n'est pas clarifié institutionnellement.
 */
class FuelController extends Controller
{
    public function __construct(private FuelService $fuel)
    {
    }

    /* ===================== TABLEAU DE BORD ===================== */

    public function index()
    {
        return view('comptable.carburant', [
            'fuel' => $this->fuel->fuelStockSummary(),
            'stockValue' => $this->fuel->fuelStockValue(),
            'pending' => $this->fuel->pendingCounts(),
            'direction' => 'comptable',
        ]);
    }

    /* ===================== ENTRÉES ===================== */

    public function entriesIndex(Request $request)
    {
        $query = FuelEntry::with('warehouse', 'creator')->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                    ->orWhere('supplier', 'like', "%{$s}%")
                    ->orWhere('invoice_number', 'like', "%{$s}%");
            });
        }

        return view('comptable.carburant.entries.index', [
            'entries' => $query->paginate(25)->withQueryString(),
            'warehouses' => Warehouse::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function entriesCreate()
    {
        return view('comptable.carburant.entries.create', [
            'warehouses' => Warehouse::orderBy('name')->pluck('name', 'id'),
            'fuelTypes' => FuelEntry::FUEL_TYPES,
        ]);
    }

    public function entriesStore(Request $request)
    {
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
        ]);

        $entry = $this->fuel->createEntry($data, Auth::user());

        return redirect()->route('comptable.carburant.entries.show', $entry->id)
            ->with('success', "Entrée {$entry->reference} créée en brouillon.");
    }

    public function entriesShow($id)
    {
        $entry = FuelEntry::with('warehouse', 'creator', 'validator', 'attachments.uploader', 'statusLogs')->findOrFail($id);
        return view('comptable.carburant.entries.show', compact('entry'));
    }

    public function entriesValidate($id)
    {
        $result = $this->fuel->validateEntry(FuelEntry::findOrFail($id), Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function entriesReject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectEntry(FuelEntry::findOrFail($id), $request->reason, Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /* ===================== SORTIES ===================== */

    public function exitsIndex(Request $request)
    {
        $query = FuelExit::with('warehouse', 'creator')->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('exit_number', 'like', "%{$s}%")
                    ->orWhere('beneficiary_name', 'like', "%{$s}%")
                    ->orWhere('vehicle_plate', 'like', "%{$s}%")
                    ->orWhere('mission_number', 'like', "%{$s}%");
            });
        }

        return view('comptable.carburant.exits.index', [
            'exits' => $query->paginate(25)->withQueryString(),
            'warehouses' => Warehouse::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function exitsCreate()
    {
        return view('comptable.carburant.exits.create', [
            'warehouses' => Warehouse::orderBy('name')->pluck('name', 'id'),
            'fuelTypes' => FuelEntry::FUEL_TYPES,
        ]);
    }

    public function exitsStore(Request $request)
    {
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
        ]);

        $exit = $this->fuel->createExit($data, Auth::user());

        return redirect()->route('comptable.carburant.exits.show', $exit->id)
            ->with('success', "Sortie {$exit->exit_number} créée en brouillon.");
    }

    public function exitsShow($id)
    {
        $exit = FuelExit::with('warehouse', 'creator', 'authorizer', 'validator', 'attachments.uploader', 'statusLogs')
            ->findOrFail($id);
        $available = $this->fuel->availableFuelStock($exit->warehouse_id, $exit->fuel_type, $exit->fuel_type_label);
        return view('comptable.carburant.exits.show', compact('exit', 'available'));
    }

    public function exitsAuthorize(Request $request, $id)
    {
        $request->validate(['quantity_authorized' => 'required|numeric|min:0.01']);
        $result = $this->fuel->authorizeExit(
            FuelExit::findOrFail($id), (float) $request->quantity_authorized, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function exitsValidate(Request $request, $id)
    {
        $request->validate(['quantity_delivered' => 'required|numeric|min:0.01']);
        $result = $this->fuel->validateExit(
            FuelExit::findOrFail($id), (float) $request->quantity_delivered, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function exitsReject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectExit(FuelExit::findOrFail($id), $request->reason, Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /* ===================== REMBOURSEMENTS ===================== */

    public function reimbsIndex(Request $request)
    {
        $query = FuelReimbursement::with('creator')->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                    ->orWhere('agent_name', 'like', "%{$s}%")
                    ->orWhere('receipt_ref', 'like', "%{$s}%")
                    ->orWhere('vehicle_plate', 'like', "%{$s}%");
            });
        }

        return view('comptable.carburant.reimbs.index', [
            'reimbs' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function reimbsCreate()
    {
        return view('comptable.carburant.reimbs.create');
    }

    public function reimbsStore(Request $request)
    {
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
        ]);

        $reimb = $this->fuel->createReimbursement($data, Auth::user());

        $flash = "Demande {$reimb->reference} créée.";
        if ($reimb->duplicate_of_ref) {
            $flash .= " Attention : doublon potentiel avec {$reimb->duplicate_of_ref}.";
        }

        return redirect()->route('comptable.carburant.reimbs.show', $reimb->id)
            ->with('success', $flash);
    }

    public function reimbsShow($id)
    {
        $reimb = FuelReimbursement::with('creator', 'reviewer', 'approver', 'payer', 'attachments.uploader', 'statusLogs')
            ->findOrFail($id);
        $canDecide = Auth::user()->role === 'admin';
        return view('comptable.carburant.reimbs.show', compact('reimb', 'canDecide'));
    }

    public function reimbsSubmit($id)
    {
        $result = $this->fuel->submitReimbursement(FuelReimbursement::findOrFail($id), Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reimbsReview(Request $request, $id)
    {
        $request->validate(['comment' => 'nullable|string|max:500']);
        $result = $this->fuel->reviewReimbursement(
            FuelReimbursement::findOrFail($id), $request->comment, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reimbsApprove(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'L\'approbation financière est réservée au rôle admin.');
        }
        $request->validate(['amount_approved' => 'required|numeric|min:0.01']);
        $result = $this->fuel->approveReimbursement(
            FuelReimbursement::findOrFail($id), (float) $request->amount_approved, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reimbsReject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $result = $this->fuel->rejectReimbursement(
            FuelReimbursement::findOrFail($id), $request->reason, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reimbsPay(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'L\'enregistrement du paiement est réservé au rôle admin.');
        }
        $request->validate([
            'payment_ref' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);
        $result = $this->fuel->payReimbursement(
            FuelReimbursement::findOrFail($id), $request->payment_ref, $request->payment_date, Auth::user()
        );
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reimbsClose($id)
    {
        $result = $this->fuel->closeReimbursement(FuelReimbursement::findOrFail($id), Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /* ===================== PIÈCES JOINTES ===================== */

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

    public function attachmentStore(Request $request, string $type, int $id)
    {
        $request->validate([
            'file' => 'required|file|max:8192|mimes:pdf,jpg,jpeg,png',
            'kind' => 'nullable|in:facture,bon_livraison,recu,ordre_mission,justificatif',
        ]);

        $subject = $this->resolveFuelSubject($type, $id);

        try {
            $attachment = $this->fuel->storeAttachment(
                $subject, $request->file('file'), Auth::user(), $request->kind ?: 'justificatif'
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', "Pièce « {$attachment->original_name} » ajoutée.");
    }

    /** Téléchargement contrôlé : le fichier n'est jamais public. */
    public function attachmentDownload($id)
    {
        $attachment = FuelAttachment::findOrFail($id);
        $disk = Storage::disk(FuelAttachment::DISK);

        if (!$disk->exists($attachment->stored_path)) {
            abort(404, 'Fichier introuvable.');
        }

        return $disk->download($attachment->stored_path, $attachment->original_name);
    }

    public function attachmentDelete($id)
    {
        $result = $this->fuel->deleteAttachment(FuelAttachment::findOrFail($id), Auth::user());
        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /* ===================== PDF & EXPORTS ===================== */

    /** PDF du bon (entrée, sortie ou remboursement) via dompdf. */
    public function pdf(string $type, int $id)
    {
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

    /** Export CSV d'un flux (entry|exit|reimb) avec les filtres courants. */
    public function exportCsv(Request $request, string $type)
    {
        [$headers, $rows, $filename] = match ($type) {
            'entry' => $this->entriesCsvData($request),
            'exit' => $this->exitsCsvData($request),
            'reimb' => $this->reimbsCsvData($request),
            default => abort(404),
        };

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function entriesCsvData(Request $request): array
    {
        $rows = FuelEntry::with('warehouse')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->orderBy('operation_date')->get()
            ->map(fn ($e) => [
                $e->reference, $e->operation_date?->format('d/m/Y'), $e->warehouse?->name,
                $e->supplier, $e->fuelLabel(), $e->quantity, $e->unit_price, $e->total_amount,
                $e->invoice_number, $e->status,
            ])->all();

        return [
            ['Référence', 'Date', 'Magasin', 'Fournisseur', 'Carburant', 'Litres', 'Prix/L', 'Total FCFA', 'Facture', 'Statut'],
            $rows, 'entrees-carburant-' . now()->format('Ymd') . '.csv',
        ];
    }

    private function exitsCsvData(Request $request): array
    {
        $rows = FuelExit::with('warehouse')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->orderBy('operation_date')->get()
            ->map(fn ($x) => [
                $x->exit_number, $x->operation_date?->format('d/m/Y'), $x->warehouse?->name,
                $x->beneficiary_name, $x->vehicle_plate, $x->fuelLabel(), $x->purpose,
                $x->quantity_requested, $x->quantity_authorized, $x->quantity_delivered, $x->status,
            ])->all();

        return [
            ['Bon', 'Date', 'Magasin', 'Bénéficiaire', 'Immat.', 'Carburant', 'Motif', 'Demandé L', 'Autorisé L', 'Délivré L', 'Statut'],
            $rows, 'sorties-carburant-' . now()->format('Ymd') . '.csv',
        ];
    }

    private function reimbsCsvData(Request $request): array
    {
        $rows = FuelReimbursement::when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('expense_date')->get()
            ->map(fn ($r) => [
                $r->reference, $r->agent_name, $r->service, $r->expense_date?->format('d/m/Y'),
                $r->purpose, $r->receipt_ref, $r->amount_claimed, $r->amount_requested,
                $r->amount_approved, $r->amount_paid, $r->advance_amount, $r->status,
            ])->all();

        return [
            ['Référence', 'Agent', 'Service', 'Date dépense', 'Motif', 'Reçu', 'Déclaré FCFA', 'Demandé FCFA', 'Approuvé FCFA', 'Payé FCFA', 'Avance FCFA', 'Statut'],
            $rows, 'remboursements-carburant-' . now()->format('Ymd') . '.csv',
        ];
    }
}
