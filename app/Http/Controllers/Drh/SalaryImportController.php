<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\RhSalaryImport;
use App\Services\Drh\SalaryImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryImportController extends Controller
{
    public function __construct(private SalaryImportService $service)
    {
    }

    public function index()
    {
        $imports = RhSalaryImport::with(['creator', 'applier'])->latest()->paginate(15);

        return view('admin.drh.remunerations.index', compact('imports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fichier' => ['required', 'file', 'max:5120', 'mimetypes:text/plain,application/octet-stream', 'extensions:txt'],
        ], [
            'fichier.extensions' => 'Le fichier doit être au format .txt.',
        ]);

        $file = $request->file('fichier');
        try {
            $import = $this->service->preview($file->get(), $file->getClientOriginalName(), Auth::id());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['fichier' => $e->getMessage()]);
        }

        return redirect()->route('admin.drh.remunerations.show', $import)
            ->with('success', 'Fichier analysé : vérifiez la prévisualisation avant de valider.');
    }

    public function show(Request $request, RhSalaryImport $import)
    {
        $rows = collect($import->rows ?? []);
        $filter = $request->query('filtre');
        if ($filter) {
            $rows = $rows->filter(fn ($r) => match ($filter) {
                'modifie', 'inchange', 'non_trouve', 'sans_matricule', 'doublon_fichier' => $r['status'] === $filter,
                'alertes' => !empty($r['warnings']),
                default => true,
            });
        }

        return view('admin.drh.remunerations.show', compact('import', 'rows', 'filter'));
    }

    public function apply(RhSalaryImport $import)
    {
        try {
            $result = $this->service->apply($import, Auth::id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = "{$result['updated']} fiche(s) agent mise(s) à jour.";
        if ($result['skipped']) {
            $message .= ' ' . count($result['skipped']) . ' ligne(s) ignorée(s) : ' . implode(' ; ', $result['skipped']);
        }

        return redirect()->route('admin.drh.remunerations.show', $import)->with('success', $message);
    }
}
