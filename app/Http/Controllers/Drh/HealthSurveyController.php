<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\HealthInsuranceSurvey;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HealthSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = HealthInsuranceSurvey::confirmed()->latest('submitted_at');

        if ($request->filled('direction')) {
            $query->where('agent_direction', $request->direction);
        }
        if ($request->filled('region')) {
            $query->where('agent_region', $request->region);
        }
        if ($request->filled('note')) {
            $query->where('q13_note', $request->note);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('agent_nom', 'LIKE', "%{$s}%")
                  ->orWhere('agent_prenom', 'LIKE', "%{$s}%")
                  ->orWhere('agent_direction', 'LIKE', "%{$s}%");
            });
        }

        $surveys = $query->paginate(25)->withQueryString();
        $stats   = $this->computeStats();

        $directions = HealthInsuranceSurvey::confirmed()
            ->whereNotNull('agent_direction')
            ->distinct()->pluck('agent_direction')->filter()->values();

        return view('admin.drh.health-survey.index', compact('surveys', 'stats', 'directions'));
    }

    public function show($id)
    {
        $survey = HealthInsuranceSurvey::findOrFail($id);
        return view('admin.drh.health-survey.show', compact('survey'));
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'enquete_assurance_maladie_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($out, [
                'Date', 'Anonyme', 'Nom', 'Prénom', 'Direction', 'Région',
                'Q1 Info', 'Q2 Documents', 'Q3 Difficultés',
                'Q4 Soins', 'Q5 Panier', 'Q6 Délais', 'Q7 Service', 'Q8 Problème',
                'Q9 Coassurance', 'Q9 Autre', 'Q10 Réseau', 'Q10 Autre',
                'Q11 Aspects', 'Q11 Autre', 'Q12 Propositions',
                'Note /5',
            ], ';');

            HealthInsuranceSurvey::confirmed()->orderBy('submitted_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->submitted_at?->format('d/m/Y H:i'),
                        $r->is_anonymous ? 'Oui' : 'Non',
                        $r->agent_nom, $r->agent_prenom, $r->agent_direction, $r->agent_region,
                        $r->q1_info_level, $r->q2_documents_clarity, $r->q3_difficulty,
                        $r->q4_soins_response, $r->q5_panier_soins, $r->q6_delais_remboursement,
                        $r->q7_service_client, $r->q8_probleme_recent,
                        $r->q9_coassurance, $r->q9_autre, $r->q10_reseau_soins, $r->q10_autre,
                        is_array($r->q11_aspects) ? implode(', ', $r->q11_aspects) : '',
                        $r->q11_autre, $r->q12_propositions,
                        $r->q13_note,
                    ], ';');
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    private function computeStats(): array
    {
        $total  = HealthInsuranceSurvey::confirmed()->count();
        $avgNote = $total > 0 ? round(HealthInsuranceSurvey::confirmed()->avg('q13_note'), 2) : 0;

        $byNote = HealthInsuranceSurvey::confirmed()->selectRaw('q13_note, COUNT(*) as c')
            ->groupBy('q13_note')->pluck('c', 'q13_note')->toArray();

        $distribution = function (string $col) {
            return HealthInsuranceSurvey::confirmed()->selectRaw("$col, COUNT(*) as c")
                ->whereNotNull($col)->groupBy($col)
                ->pluck('c', $col)->toArray();
        };

        return [
            'total'    => $total,
            'avg_note' => $avgNote,
            'by_note'  => $byNote,
            'q1'  => $distribution('q1_info_level'),
            'q2'  => $distribution('q2_documents_clarity'),
            'q3'  => $distribution('q3_difficulty'),
            'q4'  => $distribution('q4_soins_response'),
            'q5'  => $distribution('q5_panier_soins'),
            'q6'  => $distribution('q6_delais_remboursement'),
            'q7'  => $distribution('q7_service_client'),
            'q9'  => $distribution('q9_coassurance'),
            'q10' => $distribution('q10_reseau_soins'),
        ];
    }
}
