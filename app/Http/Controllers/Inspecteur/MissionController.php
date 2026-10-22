<?php

namespace App\Http\Controllers\Inspecteur;

use App\Http\Controllers\Controller;
use App\Models\RequestMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Portail Inspecteur : missions d'inspection affectées par le DTL,
 * prise en charge, rapport de visite et pièces justificatives.
 * L'inspecteur ne voit que SES missions (jamais les dossiers non autorisés).
 */
class MissionController extends Controller
{
    public function index()
    {
        $missions = RequestMission::where('assigned_to', Auth::id())
            ->where('type', RequestMission::TYPE_INSPECTION)
            ->with('request')
            ->orderByRaw("FIELD(status, 'affectee', 'en_cours', 'rapport_soumis', 'terminee', 'annulee')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'affectees' => RequestMission::where('assigned_to', Auth::id())->where('status', 'affectee')->count(),
            'en_cours' => RequestMission::where('assigned_to', Auth::id())->where('status', 'en_cours')->count(),
            'terminees' => RequestMission::where('assigned_to', Auth::id())->whereIn('status', ['rapport_soumis', 'terminee'])->count(),
        ];

        return view('inspecteur.missions.index', [
            'missions' => $missions,
            'stats' => $stats,
            'direction' => 'inspecteur',
        ]);
    }

    public function show($id)
    {
        $mission = RequestMission::where('assigned_to', Auth::id())
            ->where('type', RequestMission::TYPE_INSPECTION)
            ->with(['request'])
            ->findOrFail($id);

        return view('inspecteur.missions.show', [
            'mission' => $mission,
            'direction' => 'inspecteur',
        ]);
    }

    /** Prendre en charge la mission */
    public function start($id)
    {
        $mission = RequestMission::where('assigned_to', Auth::id())
            ->where('type', RequestMission::TYPE_INSPECTION)
            ->findOrFail($id);

        if ($mission->status !== 'affectee') {
            return redirect()->back()->with('error', 'Cette mission ne peut pas être prise en charge.');
        }

        $mission->update(['status' => 'en_cours']);
        $mission->request?->logWorkflowAction('Mission démarrée', "Inspection #{$mission->id} prise en charge par " . Auth::user()->name, Auth::id());

        return redirect()->back()->with('success', 'Mission prise en charge.');
    }

    /** Soumettre le rapport de visite au DTL */
    public function submitReport(Request $request, $id)
    {
        $request->validate([
            'report' => 'required|string|min:20|max:10000',
            'attachments.*' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $mission = RequestMission::where('assigned_to', Auth::id())
            ->where('type', RequestMission::TYPE_INSPECTION)
            ->findOrFail($id);

        if (!in_array($mission->status, ['en_cours', 'affectee'], true)) {
            return redirect()->back()->with('error', 'Rapport déjà soumis ou mission clôturée.');
        }

        try {
            DB::transaction(function () use ($request, $mission) {
                $files = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $files[] = $file->store('missions/rapports', 'public');
                    }
                }

                $mission->update([
                    'report' => $request->report,
                    'attachments' => $files,
                    'visit_latitude' => $request->latitude,
                    'visit_longitude' => $request->longitude,
                    'visited_at' => now(),
                    'reported_at' => now(),
                    'status' => 'rapport_soumis',
                ]);

                $mission->request?->logWorkflowAction(
                    'Rapport inspection soumis',
                    "Rapport de la mission #{$mission->id} transmis au DTL par " . Auth::user()->name,
                    Auth::id()
                );

                // Notifier le DTL
                try {
                    $dtlUsers = \App\Models\User::where('role', 'dtl')->get();
                    foreach ($dtlUsers as $dtl) {
                        \App\Models\Notification::create([
                            'type' => 'rapport_inspection',
                            'title' => 'Rapport d\'inspection soumis',
                            'message' => "Rapport de la mission #{$mission->id} (dossier {$mission->request?->tracking_code}) soumis par " . Auth::user()->name,
                            'user_id' => $dtl->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Notification rapport DTL échouée: ' . $e->getMessage());
                }
            });
        } catch (\Exception $e) {
            Log::error('Erreur rapport inspection: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la soumission du rapport.');
        }

        return redirect()->route('inspecteur.missions.index')->with('success', 'Rapport transmis au DTL.');
    }
}
