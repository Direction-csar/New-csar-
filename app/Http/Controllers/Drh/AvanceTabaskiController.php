<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\AgentTabaski;
use App\Models\AvanceTabaski;
use App\Models\TabaskiConfig;
use Illuminate\Http\Request;

class AvanceTabaskiController extends Controller
{
    public function index(Request $request)
    {
        $query = AvanceTabaski::with('agent')->latest('date_inscription');

        if ($request->filled('direction')) {
            $query->whereHas('agent', fn($q) => $q->where('direction', $request->direction));
        }
        if ($request->filled('region')) {
            $query->whereHas('agent', fn($q) => $q->where('region', $request->region));
        }
        if ($request->filled('montant')) {
            $query->where('montant', $request->montant);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('agent', function ($q) use ($s) {
                $q->where('nom', 'LIKE', "%{$s}%")
                  ->orWhere('prenom', 'LIKE', "%{$s}%");
            });
        }

        $inscriptions = $query->paginate(30)->withQueryString();

        $stats = [
            'total'    => AvanceTabaski::count(),
            'agents'   => AgentTabaski::count(),
            'taux'     => AgentTabaski::count() > 0 ? round(AvanceTabaski::count() / AgentTabaski::count() * 100) : 0,
            'total_100' => AvanceTabaski::where('montant', '100000')->count(),
            'total_150' => AvanceTabaski::where('montant', '150000')->count(),
            'total_200' => AvanceTabaski::where('montant', '200000')->count(),
            'montant_global' => number_format(
                AvanceTabaski::pluck('montant')->map(fn($m) => (int) $m)->sum(),
                0, ',', ' '
            ) . ' FCFA',
        ];

        $directions = AgentTabaski::distinct()->orderBy('direction')->pluck('direction');
        $regions    = AgentTabaski::distinct()->orderBy('region')->pluck('region');
        $config     = [
            'date_expiration'       => TabaskiConfig::get('date_expiration', '2026-04-22 23:59:59'),
            'inscriptions_ouvertes' => TabaskiConfig::get('inscriptions_ouvertes', '1'),
            'est_ferme'             => TabaskiConfig::estFerme(),
        ];

        return view('admin.drh.avances-tabaski.index', compact('inscriptions', 'stats', 'directions', 'regions', 'config'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'date_expiration'       => 'required|date',
            'inscriptions_ouvertes' => 'required|in:0,1',
        ]);

        TabaskiConfig::set('date_expiration', date('Y-m-d 23:59:59', strtotime($request->date_expiration)));
        TabaskiConfig::set('inscriptions_ouvertes', $request->inscriptions_ouvertes);

        return redirect()->route('admin.drh.tabaski.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function exportCsv(Request $request)
    {
        $query = AvanceTabaski::with('agent')->latest('date_inscription');
        if ($request->filled('direction')) {
            $query->whereHas('agent', fn($q) => $q->where('direction', $request->direction));
        }
        if ($request->filled('region')) {
            $query->whereHas('agent', fn($q) => $q->where('region', $request->region));
        }
        if ($request->filled('montant')) {
            $query->where('montant', $request->montant);
        }

        $inscriptions = $query->get();

        $filename = 'avances-tabaski-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($inscriptions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['N°', 'Prénom', 'Nom', 'Poste', 'Direction', 'Région', 'Montant (FCFA)', 'Date inscription'], ';');
            foreach ($inscriptions as $i => $ins) {
                fputcsv($file, [
                    $i + 1,
                    $ins->agent->prenom ?? '',
                    $ins->agent->nom ?? '',
                    $ins->agent->poste ?? '',
                    $ins->agent->direction ?? '',
                    $ins->agent->region ?? '',
                    number_format((int) $ins->montant, 0, ',', ' '),
                    $ins->date_inscription->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $inscriptions = AvanceTabaski::with('agent')->latest('date_inscription')->get();
        $stats = [
            'total'          => $inscriptions->count(),
            'montant_global' => number_format($inscriptions->sum(fn($i) => (int) $i->montant), 0, ',', ' ') . ' FCFA',
        ];
        return view('admin.drh.avances-tabaski.print', compact('inscriptions', 'stats'));
    }
}
