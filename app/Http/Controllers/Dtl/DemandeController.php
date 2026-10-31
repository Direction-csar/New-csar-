<?php

namespace App\Http\Controllers\Dtl;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Models\RequestMission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Portail DTL : dossiers approuvés transmis par la Direction,
 * instructions opérationnelles et affectation des missions
 * (inspecteurs terrain et magasiniers).
 */
class DemandeController extends Controller
{
    /** Dossiers approuvés transmis au DTL */
    public function index()
    {
        $demandes = PublicRequest::whereNotNull('dtl_transmitted_at')
            ->with(['missions.assignee'])
            ->orderBy('dtl_transmitted_at', 'desc')
            ->paginate(15);

        $stats = [
            'transmises' => PublicRequest::whereNotNull('dtl_transmitted_at')->count(),
            'a_instruire' => PublicRequest::whereNotNull('dtl_transmitted_at')
                ->whereDoesntHave('missions')->count(),
            'missions_actives' => RequestMission::whereIn('status', ['affectee', 'en_cours'])->count(),
            'cloturees' => PublicRequest::where('workflow_status', 'cloturee')->count(),
        ];

        return view('dtl.demandes.index', [
            'demandes' => $demandes,
            'stats' => $stats,
            'direction' => 'dtl',
        ]);
    }

    /** Détail d'un dossier transmis + liste des missions */
    public function show($id)
    {
        $demande = PublicRequest::whereNotNull('dtl_transmitted_at')
            ->with(['missions.assignee', 'missions.assignedBy', 'dgApprover'])
            ->findOrFail($id);

        $inspecteurs = User::where('role', 'inspecteur')->where('is_active', true)->get();
        $magasiniers = User::where('role', 'magasinier')->where('is_active', true)->get();

        return view('dtl.demandes.show', [
            'demande' => $demande,
            'inspecteurs' => $inspecteurs,
            'magasiniers' => $magasiniers,
            'direction' => 'dtl',
        ]);
    }

    /** Instruction opérationnelle générale du DTL sur le dossier */
    public function instruct(Request $request, $id)
    {
        $request->validate([
            'instructions' => 'required|string|max:3000',
        ]);

        $demande = PublicRequest::whereNotNull('dtl_transmitted_at')->findOrFail($id);
        $demande->update(['dtl_instructions' => $request->instructions]);
        $demande->logWorkflowAction('Instruction DTL', $request->instructions, Auth::id());

        return redirect()->back()->with('success', 'Instruction enregistrée sur le dossier.');
    }

    /** Affecter une mission (inspection ou sortie de stock) */
    public function storeMission(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:inspection,stock_exit',
            'assigned_to' => 'required|exists:users,id',
            'instructions' => 'required|string|max:3000',
        ]);

        $demande = PublicRequest::whereNotNull('dtl_transmitted_at')
            ->whereIn('workflow_status', ['approuvee'])
            ->findOrFail($id);

        $assignee = User::findOrFail($request->assigned_to);
        $expectedRole = $request->type === RequestMission::TYPE_INSPECTION ? 'inspecteur' : 'magasinier';
        if ($assignee->role !== $expectedRole) {
            return redirect()->back()->withInput()
                ->with('error', "La mission « {$request->type} » doit être affectée à un utilisateur de rôle « {$expectedRole} ».");
        }

        try {
            DB::transaction(function () use ($demande, $request, $assignee) {
                $demande = PublicRequest::whereKey($demande->id)->lockForUpdate()->firstOrFail();
                abort_unless($demande->workflow_status === 'approuvee', 409, 'Le dossier ne permet plus de nouvelle mission.');
                $mission = RequestMission::create([
                    'public_request_id' => $demande->id,
                    'type' => $request->type,
                    'assigned_by' => Auth::id(),
                    'assigned_to' => $assignee->id,
                    'instructions' => $request->instructions,
                    'status' => 'affectee',
                ]);

                $demande->logWorkflowAction(
                    'Mission affectée',
                    "Mission {$mission->type} #{$mission->id} affectée à {$assignee->name} par le DTL",
                    Auth::id()
                );

                // Notification interne au destinataire
                try {
                    \App\Models\Notification::create([
                        'type' => 'mission_affectee',
                        'title' => 'Nouvelle mission',
                        'message' => "Mission {$mission->type_label} #{$mission->id} sur le dossier {$demande->tracking_code}.",
                        'user_id' => $assignee->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Notification mission échouée: ' . $e->getMessage());
                }
            });
        } catch (\Exception $e) {
            Log::error('Erreur création mission: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de la mission.');
        }

        return redirect()->back()->with('success', 'Mission créée et affectée.');
    }

    /** Valider le rapport soumis : la mission est terminée */
    public function validateMission($missionId)
    {
        $mission = RequestMission::with('request')->findOrFail($missionId);
        if ($mission->status !== 'rapport_soumis') {
            return redirect()->back()->with('error', 'Seul un rapport soumis peut être validé.');
        }

        DB::transaction(function () use ($mission) {
            $mission->update([
                'status' => 'terminee',
                'completed_at' => now(),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            $mission->request?->logWorkflowAction(
                'Rapport validé par le DTL',
                "Mission #{$mission->id} : rapport accepté et mission clôturée.",
                Auth::id()
            );
            $this->notifyAssignee($mission, 'Rapport validé',
                "Votre rapport sur la mission #{$mission->id} a été validé par le DTL.");
        });

        return redirect()->back()->with('success', 'Rapport validé, mission terminée.');
    }

    /** Demander une correction : le rapport repasse en cours avec consigne */
    public function requestCorrection(Request $request, $missionId)
    {
        $request->validate(['review_comment' => 'required|string|max:2000']);

        $mission = RequestMission::with('request')->findOrFail($missionId);
        if ($mission->status !== 'rapport_soumis') {
            return redirect()->back()->with('error', 'Seul un rapport soumis peut faire l\'objet d\'une correction.');
        }

        DB::transaction(function () use ($mission, $request) {
            $mission->update([
                'status' => 'en_cours',
                'review_comment' => $request->review_comment,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            $mission->request?->logWorkflowAction(
                'Correction demandée par le DTL',
                "Mission #{$mission->id} : {$request->review_comment}",
                Auth::id()
            );
            $this->notifyAssignee($mission, 'Correction demandée',
                "Mission #{$mission->id} : {$request->review_comment}");
        });

        return redirect()->back()->with('success', 'Correction demandée à l\'agent.');
    }

    /** Notification interne à l'agent affecté (best effort) */
    private function notifyAssignee(RequestMission $mission, string $title, string $message): void
    {
        try {
            \App\Models\Notification::create([
                'type' => 'mission_revue',
                'title' => $title,
                'message' => $message,
                'user_id' => $mission->assigned_to,
            ]);
        } catch (\Exception $e) {
            Log::warning('Notification revue mission échouée: ' . $e->getMessage());
        }
    }

    /** Annuler une mission (DTL ou admin) */
    public function cancelMission(Request $request, $missionId)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $mission = RequestMission::with('request')->findOrFail($missionId);
        if (in_array($mission->status, ['terminee', 'annulee'], true)) {
            return redirect()->back()->with('error', 'Mission déjà clôturée.');
        }

        $mission->update(['status' => 'annulee']);
        $mission->request?->logWorkflowAction(
            'Mission annulée',
            "Mission #{$mission->id} annulée par le DTL : {$request->reason}",
            Auth::id()
        );

        return redirect()->back()->with('success', 'Mission annulée.');
    }
}
