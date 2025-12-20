<?php

namespace App\Http\Controllers\DRH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\WorkAttendance;
use App\Models\Personnel;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Afficher la liste des présences
     */
    public function index()
    {
        try {
            $attendance = WorkAttendance::with('personnel')
                ->orderBy('date', 'desc')
                ->paginate(15);

            return view('drh.attendance.index', compact('attendance'));

        } catch (\Exception $e) {
            Log::error('Erreur dans DRH AttendanceController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return view('drh.attendance.index', ['attendance' => collect()]);
        }
    }

    /**
     * Formulaire création présence
     */
    public function create()
    {
        try {
            $personnel = Personnel::orderBy('prenoms_nom')->get();
            return view('drh.attendance.create', compact('personnel'));
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH AttendanceController@create', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->route('drh.attendance.index')
                ->withErrors(['email' => 'Impossible d’ouvrir le formulaire de présence.']);
        }
    }

    /**
     * Enregistrer présence
     */
    public function store(Request $request)
    {
        $request->validate([
            'personnel_id' => ['required', 'integer', 'exists:personnel,id'],
            'date' => ['required', 'date'],
            'statut' => ['required', 'string'],
            'heure_arrivee' => ['nullable', 'date_format:H:i'],
            'heure_depart' => ['nullable', 'date_format:H:i'],
            'commentaires' => ['nullable', 'string'],
        ]);

        try {
            $date = Carbon::parse($request->input('date'))->toDateString();
            $statut = $request->input('statut');

            $heureArrivee = $request->input('heure_arrivee');
            $heureDepart = $request->input('heure_depart');

            $minutes = 0;
            if (($statut === 'present' || $statut === 'retard') && $heureArrivee && $heureDepart) {
                $arrivee = Carbon::parse($date . ' ' . $heureArrivee);
                $depart = Carbon::parse($date . ' ' . $heureDepart);
                $minutes = max(0, $arrivee->diffInMinutes($depart, false));
            }

            WorkAttendance::create([
                'personnel_id' => (int) $request->input('personnel_id'),
                'date' => $date,
                'heure_arrivee' => $heureArrivee ? Carbon::parse($date . ' ' . $heureArrivee) : null,
                'heure_depart' => $heureDepart ? Carbon::parse($date . ' ' . $heureDepart) : null,
                'statut' => $statut,
                'justification' => $request->input('commentaires'),
                'heures_travaillees' => $minutes,
                'valide' => true,
                'valide_par' => auth()->id(),
                'date_validation' => now(),
            ]);

            return redirect()->route('drh.attendance.index')->with('success', 'Présence enregistrée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH AttendanceController@store', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withInput()->withErrors(['email' => 'Erreur lors de l’enregistrement de la présence.']);
        }
    }

    /**
     * Non implémenté (évite 500 si on clique)
     */
    public function edit(WorkAttendance $attendance)
    {
        return redirect()->route('drh.attendance.index')
            ->with('success', 'Édition non implémentée (bientôt disponible).');
    }

    public function update(Request $request, WorkAttendance $attendance)
    {
        return redirect()->route('drh.attendance.index')
            ->with('success', 'Mise à jour non implémentée (bientôt disponible).');
    }

    public function destroy(WorkAttendance $attendance)
    {
        try {
            $attendance->delete();
            return redirect()->route('drh.attendance.index')->with('success', 'Enregistrement supprimé.');
        } catch (\Exception $e) {
            Log::error('Erreur dans DRH AttendanceController@destroy', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withErrors(['email' => 'Erreur lors de la suppression.']);
        }
    }
}

