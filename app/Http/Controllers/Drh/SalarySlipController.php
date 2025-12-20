<?php

namespace App\Http\Controllers\DRH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SalarySlip;
use App\Models\Personnel;
use Carbon\Carbon;

class SalarySlipController extends Controller
{
    /**
     * Afficher la liste des fiches de paie
     */
    public function index()
    {
        try {
            $salarySlips = SalarySlip::with('personnel')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('drh.salary-slips.index', compact('salarySlips'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DRH SalarySlipController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return view('drh.salary-slips.index', ['salarySlips' => collect()]);
        }
    }

    /**
     * Formulaire création bulletin
     */
    public function create()
    {
        try {
            $personnel = Personnel::orderBy('prenoms_nom')->get();
            return view('drh.salary-slips.create', compact('personnel'));
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH SalarySlipController@create', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->route('drh.salary-slips.index')
                ->withErrors(['email' => 'Impossible d’ouvrir le formulaire de bulletin de paie.']);
        }
    }

    /**
     * Enregistrer bulletin
     */
    public function store(Request $request)
    {
        $request->validate([
            'personnel_id' => ['required', 'integer', 'exists:personnel,id'],
            'statut' => ['required', 'string'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date'],
            'salaire_brut' => ['required', 'numeric', 'min:0'],
            'prime' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $personnelId = (int) $request->input('personnel_id');
            $debut = Carbon::parse($request->input('periode_debut'));
            $fin = Carbon::parse($request->input('periode_fin'));

            $brut = (float) $request->input('salaire_brut');
            $prime = (float) ($request->input('prime', 0) ?? 0);
            $deduction = (float) ($request->input('deduction', 0) ?? 0);
            $net = $brut + $prime - $deduction;

            $numero = SalarySlip::genererNumeroBulletin($personnelId, $debut);

            SalarySlip::create([
                'personnel_id' => $personnelId,
                'numero_bulletin' => $numero,
                'periode_debut' => $debut->toDateString(),
                'periode_fin' => $fin->toDateString(),
                'salaire_brut' => $brut,
                'salaire_net' => $net,
                // Mapping minimal (la vue utilise prime/deduction, le modèle a "autres_indemnites/autres_deductions")
                'autres_indemnites' => $prime,
                'autres_deductions' => $deduction,
                'cnss' => 0,
                'impot' => 0,
                'indemnite_logement' => 0,
                'indemnite_transport' => 0,
                'indemnite_fonction' => 0,
                'jours_travailles' => 0,
                'jours_conges' => 0,
                'jours_absences' => 0,
                'statut' => $request->input('statut'),
                'cree_par' => auth()->id(),
            ]);

            return redirect()->route('drh.salary-slips.index')->with('success', 'Bulletin de paie créé.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH SalarySlipController@store', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withInput()->withErrors(['email' => 'Erreur lors de la création du bulletin.']);
        }
    }

    /**
     * Safe placeholders (évite 500 si cliqué)
     */
    public function show(SalarySlip $salary_slip)
    {
        return redirect()->route('drh.salary-slips.index')->with('success', 'Détail non implémenté.');
    }

    public function edit(SalarySlip $salary_slip)
    {
        return redirect()->route('drh.salary-slips.index')->with('success', 'Édition non implémentée.');
    }

    public function update(Request $request, SalarySlip $salary_slip)
    {
        return redirect()->route('drh.salary-slips.index')->with('success', 'Mise à jour non implémentée.');
    }

    public function destroy(SalarySlip $salary_slip)
    {
        try {
            $salary_slip->delete();
            return redirect()->route('drh.salary-slips.index')->with('success', 'Bulletin supprimé.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH SalarySlipController@destroy', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withErrors(['email' => 'Erreur lors de la suppression.']);
        }
    }
}

