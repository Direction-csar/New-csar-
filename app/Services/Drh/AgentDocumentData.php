<?php

namespace App\Services\Drh;

use App\Models\Personnel;

/**
 * Valeurs par défaut des documents RH dérivées de la fiche agent.
 * Utilisé par le formulaire de document et par la synchronisation
 * fiche agent → documents existants.
 */
class AgentDocumentData
{
    public function defaults(?Personnel $agent): array
    {
        if (!$agent) {
            return [];
        }

        $noms = $this->splitNomPrenoms($agent->prenoms_nom);

        return [
            'date_embauche'        => $agent->date_recrutement_csar?->format('Y-m-d'),
            'date_debut'           => $agent->date_recrutement_csar?->format('Y-m-d'),
            'date_fin'             => $agent->type_contrat === 'CDD' ? '2026-12-31' : null,
            'salaire_base'         => $agent->salaire_base,
            'sursalaire'           => $agent->sursalaire,
            'indemnite_transport'  => $agent->indemnite_transport,
            'indemnite_fonction'   => $agent->indemnite_fonction,
            'salaire_brut'         => $agent->salaire_brut,
            'poste'                => $agent->poste_actuel,
            'direction'            => $agent->direction_service,
            'categorie'            => $agent->categorie,
            'diplome'              => $agent->formations_professionnelles ?? $agent->diplome_academique,
            'filiation'            => $agent->filiation,
            'numero_identification'=> $agent->numero_cni,
            'date_delivrance_id'   => $agent->date_delivrance_id?->format('Y-m-d'),
            'domicile_actuel'      => $agent->adresse_complete,
            'situation_famille'    => $agent->situation_matrimoniale,
            'nombre_epouses'       => $agent->nombre_epouses,
            // Champs déclaration de mouvement du travailleur
            'numero_immatriculation' => $agent->matricule,
            'nom'                    => $noms['nom'] ?? '',
            'prenoms'                => $noms['prenoms'] ?? '',
            'sexe'                   => in_array($agent->sexe, ['Feminin', 'Féminin', 'F'], true) ? 'F' : ($agent->sexe ? 'M' : ''),
            'pays'                   => 'Sénégal',
            'pere'                   => $this->splitFiliation($agent->filiation)['pere'],
            'mere'                   => $this->splitFiliation($agent->filiation)['mere'],
            'date'                   => now()->format('Y-m-d'),
            'horaire_hebdomadaire'   => '40',
            'duree_contrat_type'     => $agent->type_contrat === 'CDD' ? 'determinee' : 'indeterminee',
            'signature_employeur'    => '',
            'date_naissance'         => $agent->date_naissance?->format('Y-m-d'),
            'lieu_naissance'         => $agent->lieu_naissance,
            'nationalite'            => $agent->nationalite,
            'adresse'                => $agent->adresse_complete,
            // Le CIN ne doit pas apparaître dans la déclaration de mouvement :
            // ces champs restent vides (saisissables manuellement si besoin).
            'carte_identite_numero'  => '',
            'date_delivrance_id'     => $agent->date_delivrance_id?->format('Y-m-d'),
            'numero_cfp_at'          => '',
            'nombre_enfants'         => $agent->nombre_enfants,
            'date_entree'            => $agent->date_recrutement_csar?->format('Y-m-d'),
            'profession'             => $agent->poste_actuel,
            'emploi_dans_entreprise' => $agent->poste_actuel,
            'type_contrat'           => $agent->type_contrat,
            'date_contrat'           => $agent->date_recrutement_csar?->format('Y-m-d'),
            'categorie'              => $agent->categorie,
            'convention_collective'  => 'du commerce et grille salariale CSAR',
            'raison_sociale_employeur' => 'Stocks de sécurité alimentaire, Régulation du marché céréalier, Gérer les risques à l\'insécurité alimentaire par des actions de résilience',
            'activite_etablissement' => 'Sécurité alimentaire et résilience',
            'salaire_base'           => $agent->salaire_base,
            'sursalaire'             => $agent->sursalaire,
            'indemnite_transport'    => $agent->indemnite_transport,
            'indemnite_fonction'     => $agent->indemnite_fonction,
            'salaire_brut_global'    => $agent->salaire_brut,
        ];
    }

    private function splitNomPrenoms(?string $fullName): array
    {
        if (!$fullName) {
            return ['nom' => '', 'prenoms' => ''];
        }
        // La fiche stocke « Prénoms NOM » : le nom est le dernier mot.
        $parts = preg_split('/\s+/u', trim($fullName));
        return [
            'nom' => array_pop($parts) ?? '',
            'prenoms' => implode(' ', $parts),
        ];
    }

    /** Filiation « De (père) et (mère) » → ['père', 'mère']. */
    private function splitFiliation(?string $filiation): array
    {
        $filiation = trim((string) $filiation);
        if (preg_match('/^(?:De|Fils de)\s+(.+?)\s+et\s+(.+)$/iu', $filiation, $m)) {
            return ['pere' => trim($m[1]), 'mere' => trim($m[2])];
        }
        return ['pere' => '', 'mere' => ''];
    }
}
