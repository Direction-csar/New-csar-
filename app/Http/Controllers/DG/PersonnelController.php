<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PersonnelController extends Controller
{
    public function index()
    {
        try {
            // Récupérer le personnel
            $personnel = Personnel::latest()->get();

            // Statistiques du personnel
            $stats = [
                'total' => Personnel::count(),
                'active' => Personnel::where('statut_validation', 'Valide')->count(),
                'managers' => Personnel::whereIn('poste_actuel', ['Directeur Général', 'Directrice Administrative', 'Responsable Logistique'])->count(),
                'on_leave' => Personnel::where('statut_validation', 'En attente')->count(),
            ];

            Log::info('Accès au personnel DG', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);

            return view('dg.personnel.index', compact('personnel', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du personnel DG', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du chargement du personnel');
        }
    }

    public function show($id)
    {
        try {
            $employee = Personnel::findOrFail($id);

            Log::info('Consultation personnel DG', [
                'user_id' => auth()->id(),
                'personnel_id' => $id,
                'timestamp' => Carbon::now()
            ]);

            return view('dg.personnel.show', compact('employee'));

        } catch (\Exception $e) {
            Log::error('Erreur lors de la consultation du personnel DG', [
                'user_id' => auth()->id(),
                'personnel_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Employé non trouvé');
        }
    }
}
