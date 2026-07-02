<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\RhDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function selection(Request $request)
    {
        if ($request->filled('personnel_id')) {
            session(['documents_personnel_id' => $request->input('personnel_id')]);
        }
        $agents = Personnel::select('id', 'prenoms_nom', 'poste_actuel')->orderBy('prenoms_nom')->get();
        $types = [
            ['label' => 'Contrat CDI',               'slug' => 'contrat-cdi',            'icon' => 'fa-file-contract', 'color' => '#2f6fed'],
            ['label' => 'Contrat CDD',               'slug' => 'contrat-cdd',            'icon' => 'fa-file-contract', 'color' => '#2f6fed'],
            ['label' => 'Contrat stagiaire',         'slug' => 'contrat-stagiaire',      'icon' => 'fa-file-contract', 'color' => '#2f6fed'],
            ['label' => 'Certificat de travail',     'slug' => 'certificat-travail',     'icon' => 'fa-file-check', 'color' => '#2f6fed'],
            ['label' => 'Attestation de travail',    'slug' => 'attestation-travail',    'icon' => 'fa-file-lines', 'color' => '#0b7f81'],
            ['label' => 'Attestation travail & salaire','slug' => 'attestation-travail-salaire','icon' => 'fa-file-invoice-dollar', 'color' => '#0b7f81'],
            ['label' => 'Abandon de poste',         'slug' => 'abandon-poste',          'icon' => 'fa-door-open', 'color' => '#ef4444'],
            ['label' => 'Notification absence injustifiée','slug' => 'notification-absence','icon' => 'fa-bell', 'color' => '#ef4444'],
            ['label' => 'Avertissement',             'slug' => 'avertissement',          'icon' => 'fa-triangle-exclamation', 'color' => '#f59e0b'],
            ['label' => 'Contrat de prêt',           'slug' => 'contrat-pret',           'icon' => 'fa-hand-holding-dollar', 'color' => '#d97706'],
            ['label' => 'Avance sur salaire',        'slug' => 'avance-salaire',         'icon' => 'fa-money-bill-wave', 'color' => '#d97706'],
            ['label' => 'Décision de congé',         'slug' => 'decision-conge',         'icon' => 'fa-file-contract', 'color' => '#5aa832'],
            ['label' => 'Demande de récupération',   'slug' => 'demande-recuperation',   'icon' => 'fa-rotate-left', 'color' => '#5aa832'],
            ['label' => 'Domiciliation salaire',     'slug' => 'domiciliation',          'icon' => 'fa-building-columns', 'color' => '#d97706'],
            ['label' => 'Ordre de mission',          'slug' => 'ordre-mission',          'icon' => 'fa-plane', 'color' => '#7c3aed'],
            ['label' => "Autorisation d'absence",   'slug' => 'autorisation-absence',   'icon' => 'fa-user-clock', 'color' => '#ec4899'],
            ['label' => 'Bon de sortie',             'slug' => 'bon-sortie',             'icon' => 'fa-door-open', 'color' => '#6b7280'],
        ];
        return view('admin.drh.documents.selection', compact('agents', 'types'));
    }

    private function getPersonnel(Request $request): ?Personnel
    {
        $id = $request->input('personnel_id') ?? session('documents_personnel_id');
        if ($id) {
            return Personnel::find($id);
        }
        return null;
    }

    private function pdf(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }

    public function showForm(Request $request, string $type)
    {
        $agent = $this->getPersonnel($request);
        $savedDocument = null;
        $savedData = [];

        if ($request->filled('document_id')) {
            $savedDocument = RhDocument::find($request->input('document_id'));
            if ($savedDocument && $savedDocument->type === $type) {
                $savedData = $savedDocument->data ?? [];
            }
        }

        $types = [
            'contrat-cdi'              => ['label' => 'Contrat CDI', 'fields' => ['date_embauche', 'salaire_base', 'sursalaire', 'indemnite_transport', 'salaire_brut', 'poste', 'categorie', 'diplome', 'filiation', 'numero_identification', 'date_delivrance_id', 'domicile_actuel', 'situation_famille', 'nombre_epouses']],
            'contrat-cdd'              => ['label' => 'Contrat CDD', 'fields' => ['date_debut', 'date_fin', 'salaire_base', 'sursalaire', 'indemnite_transport', 'salaire_brut', 'poste', 'direction', 'categorie', 'diplome', 'filiation', 'numero_identification', 'date_delivrance_id', 'domicile_actuel', 'situation_famille', 'nombre_epouses']],
            'contrat-stagiaire'        => ['label' => 'Contrat stagiaire', 'fields' => ['date_debut', 'date_fin', 'poste', 'duree', 'gratification', 'tuteur', 'poste_tuteur', 'etablissement', 'niveau_etudes', 'domaine']],
            'certificat-travail'       => ['label' => 'Certificat de travail', 'fields' => []],
            'attestation-travail'      => ['label' => 'Attestation de travail', 'fields' => ['date_debut', 'objet']],
            'attestation-travail-salaire'=> ['label' => 'Attestation travail & salaire', 'fields' => ['date_debut', 'salaire_net', 'periode']],
            'abandon-poste'            => ['label' => 'Abandon de poste', 'fields' => ['date_abandon', 'motif', 'biens_rendus']],
            'notification-absence'     => ['label' => 'Notification absence injustifiée', 'fields' => ['date_absence', 'nombre_jours', 'sanction']],
            'avertissement'            => ['label' => 'Avertissement', 'fields' => ['date_fait', 'motif', 'sanction']],
            'contrat-pret'             => ['label' => 'Contrat de prêt', 'fields' => ['montant', 'duree_remboursement', 'taux', 'date_premiere_echeance']],
            'avance-salaire'           => ['label' => 'Avance sur salaire', 'fields' => ['montant', 'mois', 'motif']],
            'decision-conge'           => ['label' => 'Décision de congé', 'fields' => ['type_conge', 'duree', 'date_debut']],
            'demande-recuperation'     => ['label' => 'Demande de récupération', 'fields' => ['date_absence', 'duree', 'date_recuperation']],
            'domiciliation'            => ['label' => 'Domiciliation salaire', 'fields' => ['numero_compte', 'date_effet', 'montant_salaire']],
            'ordre-mission'            => ['label' => 'Ordre de mission', 'fields' => ['prenoms', 'nom', 'grade', 'direction', 'situation', 'destination', 'motif', 'date_depart', 'date_retour', 'transport', 'imputation']],
            'autorisation-absence'     => ['label' => 'Autorisation d\'absence', 'fields' => ['date_absence', 'duree', 'duree_lettres', 'motif', 'consequence', 'cumul_ant', 'cumul_jour', 'observations']],
            'bon-sortie'               => ['label' => 'Bon de sortie', 'fields' => ['date_sortie', 'heure_sortie', 'motif', 'autorise_par']],
        ];

        if (!isset($types[$type])) {
            abort(404);
        }

        return view('admin.drh.documents.form', [
            'type'      => $type,
            'docInfo'   => $types[$type],
            'fields'    => $types[$type]['fields'] ?? [],
            'agent'     => $agent,
            'savedDocument' => $savedDocument,
            'savedData' => $savedData,
        ]);
    }

    public function certificatTravail(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.certificat_travail', array_merge(['personnel' => $agent], $request->all()), 'certificat-travail.pdf');
    }

    public function noteService(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.note_service', array_merge(['personnel' => $agent], $request->all()), 'note-service.pdf');
    }

    public function decisionConge(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.decision_conge', array_merge(['personnel' => $agent], $request->all()), 'decision-conge.pdf');
    }

    public function domiciliation(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.domiciliation', array_merge(['personnel' => $agent], $request->all()), 'domiciliation-salaire.pdf');
    }

    public function ordreMission(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.ordre_mission', array_merge(['personnel' => $agent], $request->all()), 'ordre-mission.pdf');
    }

    public function autorisationAbsence(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.autorisation_absence', array_merge(['personnel' => $agent], $request->all()), 'autorisation-absence.pdf');
    }

    public function contratCdi(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.contrat_cdi', array_merge(['personnel' => $agent], $request->all()), 'contrat-cdi.pdf');
    }

    public function contratCdd(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.contrat_cdd', array_merge(['personnel' => $agent], $request->all()), 'contrat-cdd.pdf');
    }

    public function attestationTravail(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.attestation_travail', array_merge(['personnel' => $agent], $request->all()), 'attestation-travail.pdf');
    }

    public function attestationTravailSalaire(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.attestation_travail_salaire', array_merge(['personnel' => $agent], $request->all()), 'attestation-travail-salaire.pdf');
    }

    public function abandonPoste(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.abandon_poste', array_merge(['personnel' => $agent], $request->all()), 'abandon-poste.pdf');
    }

    public function notificationAbsence(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.notification_absence', array_merge(['personnel' => $agent], $request->all()), 'notification-absence.pdf');
    }

    public function avertissement(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.avertissement', array_merge(['personnel' => $agent], $request->all()), 'avertissement.pdf');
    }

    public function contratPret(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.contrat_pret', array_merge(['personnel' => $agent], $request->all()), 'contrat-pret.pdf');
    }

    public function avanceSalaire(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.avance_salaire', array_merge(['personnel' => $agent], $request->all()), 'avance-salaire.pdf');
    }

    public function demandeRecuperation(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.demande_recuperation', array_merge(['personnel' => $agent], $request->all()), 'demande-recuperation.pdf');
    }

    public function bonSortie(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.bon_sortie', array_merge(['personnel' => $agent], $request->all()), 'bon-sortie.pdf');
    }

    public function contratStagiaire(Request $request)
    {
        $agent = $this->getPersonnel($request);
        return $this->pdf('admin.drh.documents.contrat_stagiaire', array_merge(['personnel' => $agent], $request->all()), 'contrat-stagiaire.pdf');
    }

    // ========== SAUVEGARDE & HISTORIQUE ==========

    public function saveDocument(Request $request, string $type)
    {
        $types = [
            'contrat-cdi'              => 'Contrat CDI',
            'contrat-cdd'              => 'Contrat CDD',
            'contrat-stagiaire'        => 'Contrat stagiaire',
            'certificat-travail'       => 'Certificat de travail',
            'attestation-travail'      => 'Attestation de travail',
            'attestation-travail-salaire' => 'Attestation travail & salaire',
            'abandon-poste'            => 'Abandon de poste',
            'notification-absence'     => 'Notification absence injustifiée',
            'avertissement'            => 'Avertissement',
            'contrat-pret'             => 'Contrat de prêt',
            'avance-salaire'           => 'Avance sur salaire',
            'decision-conge'           => 'Décision de congé',
            'demande-recuperation'     => 'Demande de récupération',
            'domiciliation'            => 'Domiciliation salaire',
            'ordre-mission'            => 'Ordre de mission',
            'autorisation-absence'     => 'Autorisation d\'absence',
            'bon-sortie'               => 'Bon de sortie',
            'note-service'             => 'Note de service',
        ];

        if (!isset($types[$type])) {
            abort(404);
        }

        $data = $request->except(['_token', 'personnel_id']);
        $personnelId = $request->input('personnel_id') ?: session('documents_personnel_id');

        $document = RhDocument::create([
            'personnel_id' => $personnelId ?: null,
            'type' => $type,
            'label' => $types[$type],
            'data' => $data,
            'statut' => 'Enregistré',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.drh.documents.form', ['type' => $type, 'document_id' => $document->id])
            ->with('success', 'Document enregistré avec succès. Vous pouvez maintenant l\'exporter.');
    }

    public function historique(Request $request)
    {
        $query = RhDocument::with(['personnel', 'creator'])->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('agent')) {
            $query->whereHas('personnel', function($q) use ($request) {
                $q->where('prenoms_nom', 'like', '%' . $request->agent . '%');
            });
        }

        $documents = $query->paginate(20)->withQueryString();

        $types = [
            'contrat-cdi' => 'Contrat CDI',
            'contrat-cdd' => 'Contrat CDD',
            'contrat-stagiaire' => 'Contrat stagiaire',
            'certificat-travail' => 'Certificat de travail',
            'attestation-travail' => 'Attestation de travail',
            'attestation-travail-salaire' => 'Attestation travail & salaire',
            'abandon-poste' => 'Abandon de poste',
            'notification-absence' => 'Notification absence',
            'avertissement' => 'Avertissement',
            'contrat-pret' => 'Contrat de prêt',
            'avance-salaire' => 'Avance sur salaire',
            'decision-conge' => 'Décision de congé',
            'demande-recuperation' => 'Demande de récupération',
            'domiciliation' => 'Domiciliation',
            'ordre-mission' => 'Ordre de mission',
            'autorisation-absence' => 'Autorisation d\'absence',
            'bon-sortie' => 'Bon de sortie',
            'note-service' => 'Note de service',
        ];

        return view('admin.drh.documents.historique', compact('documents', 'types'));
    }

    public function exportDocument(RhDocument $document)
    {
        $agent = $document->personnel;
        $data = array_merge(['personnel' => $agent], $document->data ?? []);

        $viewMap = [
            'contrat-cdi' => 'admin.drh.documents.contrat_cdi',
            'contrat-cdd' => 'admin.drh.documents.contrat_cdd',
            'contrat-stagiaire' => 'admin.drh.documents.contrat_stagiaire',
            'certificat-travail' => 'admin.drh.documents.certificat_travail',
            'attestation-travail' => 'admin.drh.documents.attestation_travail',
            'attestation-travail-salaire' => 'admin.drh.documents.attestation_travail_salaire',
            'abandon-poste' => 'admin.drh.documents.abandon_poste',
            'notification-absence' => 'admin.drh.documents.notification_absence',
            'avertissement' => 'admin.drh.documents.avertissement',
            'contrat-pret' => 'admin.drh.documents.contrat_pret',
            'avance-salaire' => 'admin.drh.documents.avance_salaire',
            'decision-conge' => 'admin.drh.documents.decision_conge',
            'demande-recuperation' => 'admin.drh.documents.demande_recuperation',
            'domiciliation' => 'admin.drh.documents.domiciliation',
            'ordre-mission' => 'admin.drh.documents.ordre_mission',
            'autorisation-absence' => 'admin.drh.documents.autorisation_absence',
            'bon-sortie' => 'admin.drh.documents.bon_sortie',
            'note-service' => 'admin.drh.documents.note_service',
        ];

        $view = $viewMap[$document->type] ?? abort(404);
        $filename = str_replace(' ', '-', strtolower($document->label)) . '-' . $document->id . '.pdf';

        $document->update([
            'statut' => 'Exporté',
            'exported_at' => now(),
        ]);

        return $this->pdf($view, $data, $filename);
    }
}
