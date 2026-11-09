<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\RhDocument;
use App\Services\Drh\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractBatchController extends Controller
{
    public function __construct(private ContractService $contracts)
    {
    }

    public function index(Request $request)
    {
        $agents = Personnel::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('prenoms_nom', 'like', '%' . $request->q . '%')
                ->orWhere('matricule', 'like', '%' . $request->q . '%')))
            ->orderBy('prenoms_nom')
            ->get(['id', 'prenoms_nom', 'matricule', 'poste_actuel', 'type_contrat', 'salaire_brut']);

        $documents = RhDocument::contracts()->whereIn('personnel_id', $agents->pluck('id'))
            ->with(['creator', 'signer'])->orderByDesc('version')->orderByDesc('id')->get()
            ->groupBy('personnel_id');

        $stats = [
            'agents' => $agents->count(),
            'avec_contrat' => $documents->filter(fn ($d) => $d->whereNull('superseded_at')->isNotEmpty())->count(),
            'signes' => $documents->filter(fn ($d) => $d->whereNull('superseded_at')->whereNotNull('signed_at')->isNotEmpty())->count(),
            'avenants' => $documents->flatten()->where('statut', RhDocument::STATUT_A_VALIDER)->whereNull('superseded_at')->count(),
        ];
        $stats['manquants'] = $stats['agents'] - $stats['avec_contrat'];

        return view('admin.drh.contrats.index', compact('agents', 'documents', 'stats'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:all,missing,selection'],
            'agents' => ['required_if:mode,selection', 'array'],
            'agents.*' => ['integer'],
        ], ['agents.required_if' => 'Sélectionnez au moins un agent.']);

        @set_time_limit(600);
        $agents = Personnel::query()
            ->when($data['mode'] === 'selection', fn ($q) => $q->whereIn('id', $data['agents']))
            ->orderBy('prenoms_nom')->get();

        $s = $this->contracts->generateMany($agents, $data['mode'], Auth::id());
        $message = "Contrats : {$s['created']} généré(s), {$s['avenants']} avenant(s) à valider, {$s['unchanged']} inchangé(s), {$s['skipped']} déjà existant(s).";

        return redirect()->route('admin.drh.contrats.index')
            ->with('success', $message)
            ->with('error', $s['errors'] ? implode(' ; ', $s['errors']) : null);
    }

    public function preview(Personnel $personnel)
    {
        return response($this->contracts->preview($personnel), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="apercu-contrat.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function download(Request $request, RhDocument $document)
    {
        abort_unless($document->isContract(), 404);
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        return response($this->contracts->pdfFor($document), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $this->contracts->filenameFor($document) . '"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function zip(Request $request)
    {
        $ids = $request->validate(['agents' => ['nullable', 'array'], 'agents.*' => ['integer']])['agents'] ?? [];
        @set_time_limit(600);

        $documents = RhDocument::contracts()->current()
            ->where('statut', '!=', RhDocument::STATUT_A_VALIDER)
            ->when($ids, fn ($q) => $q->whereIn('personnel_id', $ids))
            ->with('personnel')->get();

        if ($documents->isEmpty()) {
            return back()->with('error', 'Aucun contrat en vigueur à télécharger.');
        }

        return response()->download($this->contracts->zip($documents), 'contrats-csar-' . now()->format('Ymd-His') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend();
    }

    public function sign(RhDocument $document)
    {
        try {
            $this->contracts->markSigned($document, Auth::id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Contrat marqué comme signé : il ne sera plus jamais écrasé.');
    }

    public function validateAvenant(RhDocument $document)
    {
        try {
            $this->contracts->validateAvenant($document);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Avenant validé : il devient le contrat en vigueur, l\'ancienne version reste archivée.');
    }
}
