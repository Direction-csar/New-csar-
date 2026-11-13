<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\RhDocument;
use App\Services\Drh\ContractService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function selection(Request $request)
    {
        $tousAgents = $request->input('personnel_id') === '__all__';
        if ($request->filled('personnel_id') && !$tousAgents) {
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
            ['label' => 'Déclaration de mouvement du travailleur', 'slug' => 'declaration-mouvement-travailleur', 'icon' => 'fa-file-signature', 'color' => '#1e40af'],
        ];
        return view('admin.drh.documents.selection', compact('agents', 'types', 'tousAgents'));
    }

    private function getPersonnel(Request $request): ?Personnel
    {
        $id = $request->input('personnel_id') ?: session('documents_personnel_id');
        if ($id) {
            return Personnel::find($id);
        }
        return null;
    }

    private function contractDefaults(?Personnel $agent): array
    {
        return app(\App\Services\Drh\AgentDocumentData::class)->defaults($agent);
    }

    private function pdf(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }

    public function showForm(Request $request, string $type)
    {
        $savedDocument = null;
        $savedData = [];

        if ($request->filled('document_id')) {
            $savedDocument = RhDocument::find($request->input('document_id'));
            if ($savedDocument && $savedDocument->type === $type) {
                $savedData = $savedDocument->data ?? [];
            } else {
                $savedDocument = null;
            }
        }

        // L'agent choisi dans le formulaire devient l'agent de session :
        // le bouton « Générer PDF » (routes directes) l'utilise aussi.
        if ($request->filled('personnel_id')) {
            session(['documents_personnel_id' => $request->input('personnel_id')]);
        }

        // En modification, l'agent du document est proposé par défaut.
        $agent = $this->getPersonnel($request) ?? $savedDocument?->personnel;

        $types = [
            'contrat-cdi'              => ['label' => 'Contrat CDI', 'fields' => ['date_embauche', 'salaire_base', 'sursalaire', 'indemnite_transport', 'indemnite_fonction', 'salaire_brut', 'poste', 'categorie', 'diplome', 'filiation', 'numero_identification', 'date_delivrance_id', 'domicile_actuel', 'situation_famille', 'nombre_epouses']],
            'contrat-cdd'              => ['label' => 'Contrat CDD', 'fields' => ['date_debut', 'date_fin', 'salaire_base', 'sursalaire', 'indemnite_transport', 'indemnite_fonction', 'salaire_brut', 'poste', 'direction', 'categorie', 'diplome', 'filiation', 'numero_identification', 'date_delivrance_id', 'domicile_actuel', 'situation_famille', 'nombre_epouses']],
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
            'declaration-mouvement-travailleur' => ['label' => 'Déclaration de mouvement du travailleur', 'fields' => []],
        ];

        if (!isset($types[$type])) {
            abort(404);
        }

        $agents = Personnel::orderBy('prenoms_nom')->get(['id', 'prenoms_nom', 'poste_actuel']);

        if ($type === 'declaration-mouvement-travailleur') {
            return view('admin.drh.documents.declaration_mouvement.form', [
                'type'      => $type,
                'docInfo'   => $types[$type],
                'agent'     => $agent,
                'agents'    => $agents,
                'savedDocument' => $savedDocument,
                'savedData' => $savedData,
                'defaults'  => $this->contractDefaults($agent),
            ]);
        }

        return view('admin.drh.documents.form', [
            'type'      => $type,
            'docInfo'   => $types[$type],
            'fields'    => $types[$type]['fields'] ?? [],
            'agent'     => $agent,
            'agents'    => $agents,
            'savedDocument' => $savedDocument,
            'savedData' => $savedData,
            'defaults'  => $this->contractDefaults($agent),
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
        $data = array_merge($this->contractDefaults($agent), $request->all(), ['personnel' => $agent]);
        return $this->pdf('admin.drh.documents.contrat_cdi', $data, 'contrat-cdi.pdf');
    }

    public function contratCdd(Request $request)
    {
        $agent = $this->getPersonnel($request);
        $data = array_merge($this->contractDefaults($agent), $request->all(), ['personnel' => $agent]);
        return $this->pdf('admin.drh.documents.contrat_cdd', $data, 'contrat-cdd.pdf');
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
            'declaration-mouvement-travailleur' => 'Déclaration de mouvement du travailleur',
        ];

        if (!isset($types[$type])) {
            abort(404);
        }

        $data = $request->except(['_token', 'personnel_id', 'document_id']);
        $personnelId = $request->input('personnel_id') ?: session('documents_personnel_id');

        if (!$personnelId) {
            return back()->with('error', 'Sélectionnez d\'abord un agent.');
        }

        // Modification d'un document existant : mise à jour en place (sauf signé/archivé).
        if ($request->filled('document_id')) {
            $document = RhDocument::findOrFail($request->input('document_id'));
            abort_unless($document->type === $type, 404);
            if ($document->isSigned() || $document->superseded_at) {
                return redirect()->route('admin.drh.documents.form', ['type' => $type, 'document_id' => $document->id])
                    ->with('error', 'Un document signé ou archivé ne peut pas être modifié.');
            }

            $document->fill([
                'data' => $data,
                'personnel_id' => $personnelId ?: $document->personnel_id,
            ])->save();

            // Contrat : régénérer le PDF archivé pour que les exports/ZIP reflètent les modifications.
            if ($document->isContract()) {
                app(ContractService::class)->refreshStoredPdf($document->fresh());
            }

            return redirect()->route('admin.drh.documents.form', ['type' => $type, 'document_id' => $document->id])
                ->with('success', 'Document mis à jour.');
        }

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
            'declaration-mouvement-travailleur' => 'Déclaration de mouvement du travailleur',
        ];

        return view('admin.drh.documents.historique', compact('documents', 'types'));
    }

    public function destroyDocument(RhDocument $document)
    {
        if ($document->isContract() && ($document->isSigned() || $document->superseded_at)) {
            return redirect()->route('admin.drh.documents.historique')
                ->with('error', 'Un contrat signé ou une version archivée ne peut pas être supprimé.');
        }

        $document->delete();

        return redirect()->route('admin.drh.documents.historique')
            ->with('success', 'Document supprimé avec succès.');
    }

    public function destroyAllDocuments()
    {
        // Les contrats signés et les versions archivées sont conservés.
        RhDocument::query()->where(fn ($q) => $q
            ->whereNotIn('type', RhDocument::CONTRACT_TYPES)
            ->orWhere(fn ($c) => $c->whereNull('signed_at')->whereNull('superseded_at')
                ->where('statut', '!=', RhDocument::STATUT_A_VALIDER)))
            ->delete();

        return redirect()->route('admin.drh.documents.historique')
            ->with('success', 'Historique supprimé (les contrats signés et les versions archivées sont conservés).');
    }

    /**
     * Génération groupée des déclarations de mouvement : crée les documents
     * manquants pour tous les agents puis télécharge un ZIP de tous les PDF.
     */
    public function generateAllDmt()
    {
        @set_time_limit(900);
        $type = 'declaration-mouvement-travailleur';

        $existing = RhDocument::where('type', $type)->whereNull('superseded_at')
            ->whereNull('signed_at')->pluck('personnel_id')->all();

        $defaults = app(\App\Services\Drh\AgentDocumentData::class);
        $created = 0;
        Personnel::orderBy('prenoms_nom')->get()->each(function (Personnel $agent) use ($existing, $defaults, $type, &$created) {
            if (in_array($agent->id, $existing, true)) {
                return;
            }
            RhDocument::create([
                'personnel_id' => $agent->id,
                'type' => $type,
                'label' => 'Déclaration de mouvement du travailleur',
                'data' => $defaults->defaults($agent),
                'statut' => 'Enregistré',
                'created_by' => Auth::id(),
            ]);
            $created++;
        });

        $documents = RhDocument::where('type', $type)->whereNull('superseded_at')
            ->whereNull('signed_at')->with('personnel')->orderBy('id')->get();
        if ($documents->isEmpty()) {
            return back()->with('error', 'Aucune déclaration n\'a pu être générée.');
        }

        // Un seul PDF pour toute la série : le HTML de chaque déclaration
        // (2 pages `.page`) est concaténé, Dompdf gère les sauts de page.
        @ini_set('memory_limit', '1024M');
        $style = '';
        $body = '';
        $count = 0;
        foreach ($documents as $doc) {
            $agent = $doc->personnel;
            if (!$agent) {
                continue;
            }
            // Les immatriculations C.C.P.F.-A.T. et A.G.R.O.M. doivent rester
            // vides : purge des anciennes valeurs (ex. CIN pré-rempli).
            $docData = $doc->data ?? [];
            if (($docData['numero_cfp_at'] ?? '') !== '' || ($docData['numero_agrom'] ?? '') !== '') {
                $docData['numero_cfp_at'] = '';
                $docData['numero_agrom'] = '';
                $doc->update(['data' => $docData]);
            }
            $data = array_merge(['personnel' => $agent], $docData);
            $html = view('admin.drh.documents.declaration_mouvement.pdf', ['data' => $data] + $data)->render();

            if ($style === '' && preg_match('/<style>(.*?)<\/style>/s', $html, $sm)) {
                $style = $sm[1];
            }
            if (preg_match('/<body>(.*)<\/body>/s', $html, $bm)) {
                $body .= $bm[1];
                $count++;
            }
        }

        if ($count === 0) {
            return back()->with('error', 'Aucun PDF n\'a pu être généré.');
        }

        session()->flash('success', "Déclarations de mouvement : {$created} créée(s), {$count} document(s) dans le PDF.");

        $pdf = Pdf::loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $style . '</style></head><body>' . $body . '</body></html>')
            ->setPaper('a4', 'portrait')
            ->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dmt-csar-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    /**
     * Génération groupée d'un type de document simple pour tous les agents :
     * crée les documents manquants puis retourne un PDF unique prêt à imprimer.
     */
    public function generateAllDocuments(string $type)
    {
        $allowed = [
            'certificat-travail' => ['label' => 'Certificat de travail', 'view' => 'admin.drh.documents.certificat_travail'],
            'attestation-travail' => ['label' => 'Attestation de travail', 'view' => 'admin.drh.documents.attestation_travail'],
            'attestation-travail-salaire' => ['label' => 'Attestation travail & salaire', 'view' => 'admin.drh.documents.attestation_travail_salaire'],
        ];
        abort_unless(isset($allowed[$type]), 404);

        @set_time_limit(900);
        @ini_set('memory_limit', '1024M');

        $defaults = app(\App\Services\Drh\AgentDocumentData::class);
        $created = 0;
        Personnel::orderBy('prenoms_nom')->get()->each(function (Personnel $agent) use ($allowed, $type, $defaults, &$created) {
            $exists = RhDocument::where('type', $type)->where('personnel_id', $agent->id)
                ->whereNull('superseded_at')->whereNull('signed_at')->exists();
            if ($exists) {
                return;
            }
            RhDocument::create([
                'personnel_id' => $agent->id,
                'type' => $type,
                'label' => $allowed[$type]['label'],
                'data' => $defaults->defaults($agent),
                'statut' => 'Enregistré',
                'created_by' => Auth::id(),
            ]);
            $created++;
        });

        $documents = RhDocument::where('type', $type)->whereNull('superseded_at')
            ->whereNull('signed_at')->with('personnel')->orderBy('id')->get();
        if ($documents->isEmpty()) {
            return back()->with('error', 'Aucun document n\'a pu être généré.');
        }

        $style = '';
        $body = '';
        $count = 0;
        foreach ($documents as $doc) {
            $agent = $doc->personnel;
            if (!$agent) {
                continue;
            }
            $data = array_merge(['personnel' => $agent], $doc->data ?? []);
            $html = view($allowed[$type]['view'], ['data' => $data] + $data)->render();

            if ($style === '' && preg_match('/<style>(.*?)<\/style>/s', $html, $sm)) {
                $style = $sm[1];
            }
            if (preg_match('/<body>(.*)<\/body>/s', $html, $bm)) {
                $body .= '<div style="page-break-after: always;">' . $bm[1] . '</div>';
                $count++;
            }
        }

        if ($count === 0) {
            return back()->with('error', 'Aucun PDF n\'a pu être généré.');
        }

        session()->flash('success', "{$allowed[$type]['label']} : {$created} créé(s), {$count} document(s) dans le PDF.");

        $pdf = Pdf::loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $style . '</style></head><body>' . $body . '</body></html>')
            ->setPaper('a4', 'portrait')
            ->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . \Illuminate\Support\Str::slug($allowed[$type]['label']) . '-csar-' . now()->format('Ymd-His') . '.pdf"',
        ]);
    }

    public function exportDocument(RhDocument $document)
    {
        $agent = $document->personnel
            ?? (session('documents_personnel_id') ? Personnel::find(session('documents_personnel_id')) : null);
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
            'declaration-mouvement-travailleur' => 'admin.drh.documents.declaration_mouvement.pdf',
        ];

        $view = $viewMap[$document->type] ?? abort(404);
        $filename = str_replace(' ', '-', strtolower($document->label)) . '-' . $document->id . '.pdf';

        $keepStatut = $document->isContract() && ($document->isSigned() || $document->isPendingValidation() || $document->superseded_at);
        $document->update($keepStatut ? ['exported_at' => now()] : [
            'statut' => 'Exporté',
            'exported_at' => now(),
        ]);

        return $this->pdf($view, ['data' => $data] + $data, $filename);
    }
}
