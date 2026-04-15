<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AgentTabaski;

class AgentsTabaskiSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AgentTabaski::truncate();
        DB::table('avance_tabaski')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $agents = [
            // DIRECTION GENERALE
            ['prenom' => 'Alioune', 'nom' => 'NDIAYE', 'poste' => 'Conseiller Technique/DG', 'direction' => 'Direction Générale', 'region' => 'Dakar'],
            ['prenom' => 'Ndeye Marème', 'nom' => 'DIOP', 'poste' => 'Conseiller Technique en Communication et Relations Publiques/DG', 'direction' => 'Direction Générale', 'region' => 'Dakar'],
            ['prenom' => 'Lamine Seye', 'nom' => 'DANFA', 'poste' => 'Photographe-Reporter', 'direction' => 'Direction Générale', 'region' => 'Dakar'],
            ['prenom' => 'Mohamed', 'nom' => 'SOW', 'poste' => 'Administrateur des Systèmes, bases de données et plateformes numériques', 'direction' => 'Direction Générale', 'region' => 'Dakar'],
            ['prenom' => 'Ousmane', 'nom' => 'THIAM', 'poste' => 'Chauffeur Directeur Général', 'direction' => 'Direction Générale', 'region' => 'Dakar'],

            // SECRETARIAT GENERAL
            ['prenom' => 'Massamba', 'nom' => 'DIOP', 'poste' => 'Secrétaire Général', 'direction' => 'Secrétariat Général', 'region' => 'Dakar'],
            ['prenom' => 'Awa', 'nom' => 'KANE', 'poste' => 'Assistante SG', 'direction' => 'Secrétariat Général', 'region' => 'Dakar'],
            ['prenom' => 'Oumar', 'nom' => 'CAMARA', 'poste' => 'Chef bureau courrier', 'direction' => 'Secrétariat Général', 'region' => 'Dakar'],
            ['prenom' => 'Ibrahima', 'nom' => 'DIA', 'poste' => 'Chauffeur', 'direction' => 'Secrétariat Général', 'region' => 'Dakar'],

            // AGENCE COMPTABLE
            ['prenom' => 'Alassane', 'nom' => 'DIAW', 'poste' => 'Agent Comptable', 'direction' => 'Agence Comptable', 'region' => 'Dakar'],

            // DIRECTION FINANCIERE ET DE LA COOPERATION
            ['prenom' => 'Yandé', 'nom' => 'NDIAYE', 'poste' => 'Directeur Financier et de la Coopération', 'direction' => 'Direction Financière et de la Coopération', 'region' => 'Dakar'],
            ['prenom' => 'Bienta', 'nom' => 'NDOYE', 'poste' => 'Chef Division Financière', 'direction' => 'Direction Financière et de la Coopération', 'region' => 'Dakar'],
            ['prenom' => 'Djiby', 'nom' => 'KANE', 'poste' => 'Chef Division Coopération', 'direction' => 'Direction Financière et de la Coopération', 'region' => 'Dakar'],
            ['prenom' => 'Papa Magatte Sogue', 'nom' => 'DIAW', 'poste' => 'Comptable matières', 'direction' => 'Direction Financière et de la Coopération', 'region' => 'Dakar'],
            ['prenom' => 'Souadou', 'nom' => 'KA', 'poste' => 'Chef Bureau Transit', 'direction' => 'Direction Financière et de la Coopération', 'region' => 'Dakar'],

            // DIRECTION DES RESSOURCES HUMAINES
            ['prenom' => 'Ibrahima', 'nom' => 'DIOP', 'poste' => 'Directeur des Ressources Humaines', 'direction' => 'Direction des Ressources Humaines', 'region' => 'Dakar'],
            ['prenom' => 'Mouhamadou Moustapha', 'nom' => 'TOURE', 'poste' => 'Division Administration du personnel', 'direction' => 'Direction des Ressources Humaines', 'region' => 'Dakar'],
            ['prenom' => 'Ndéye Ngoné', 'nom' => 'BAO', 'poste' => 'Bureau Gestion Relation Sociale', 'direction' => 'Direction des Ressources Humaines', 'region' => 'Dakar'],
            ['prenom' => 'Aliou', 'nom' => 'BA', 'poste' => 'Gardien', 'direction' => 'Direction des Ressources Humaines', 'region' => 'Dakar'],
            ['prenom' => 'Mame Libass Laye', 'nom' => 'FAYE', 'poste' => 'Gardien', 'direction' => 'Direction des Ressources Humaines', 'region' => 'Dakar'],

            // DIRECTION TECHNIQUE ET LOGISTIQUE
            ['prenom' => 'Daour', 'nom' => 'NDIAYE', 'poste' => 'Directeur Technique et Logistique', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Moussa', 'nom' => 'DIOP', 'poste' => 'Division Logistique', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Khoudia', 'nom' => 'DIAW', 'poste' => 'Division Technique', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Inadone', 'nom' => 'KANE', 'poste' => 'Chef Bureau Gestion des stocks', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'El Hadji Malick', 'nom' => 'NDIAYE', 'poste' => 'Chef Bureau qualité', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Issac', 'nom' => 'NDIAYE', 'poste' => 'Chef bureau gestion infra', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Baboucar', 'nom' => 'COLY', 'poste' => 'Chauffeur', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],
            ['prenom' => 'Babou', 'nom' => 'THIAM', 'poste' => 'Chauffeur', 'direction' => 'Direction Technique et Logistique', 'region' => 'Dakar'],

            // DIRECTION DE LA SECURITE ALIMENTAIRE ET DE LA RESILIENCE
            ['prenom' => 'Dieynaba', 'nom' => 'BA', 'poste' => 'Division Système Information des Marchés', 'direction' => 'Direction de la Sécurité Alimentaire et de la Résilience', 'region' => 'Dakar'],
            ['prenom' => 'Coumba Kane', 'nom' => 'SOW', 'poste' => 'Chef bureau Zone à Risque Alimentaire', 'direction' => 'Direction de la Sécurité Alimentaire et de la Résilience', 'region' => 'Dakar'],
            ['prenom' => 'Hawa', 'nom' => 'SOW', 'poste' => 'Spécialiste en prévention et gestion des risques liés à la Sécurité alimentaire', 'direction' => 'Direction de la Sécurité Alimentaire et de la Résilience', 'region' => 'Dakar'],

            // DIRECTION DE LA PLANIFICATION
            ['prenom' => 'Iba', 'nom' => 'DIOP', 'poste' => 'Directeur de la planification', 'direction' => 'Direction de la Planification', 'region' => 'Dakar'],

            // CELLULE INFORMATIQUE
            ['prenom' => 'Maty', 'nom' => 'DIOP', 'poste' => 'Chef cellule Informatique', 'direction' => 'Cellule Informatique', 'region' => 'Dakar'],
            ['prenom' => 'Lamine Ndiaye', 'nom' => 'CISSE', 'poste' => 'Assistant Cellule Informatique', 'direction' => 'Cellule Informatique', 'region' => 'Dakar'],

            // CELLULE PASSATION MARCHES
            ['prenom' => 'Awa', 'nom' => 'GUIRO', 'poste' => 'Chef de cellule Passation marchés', 'direction' => 'Cellule Passation Marchés', 'region' => 'Dakar'],

            // INSPECTION REGIONALE DE DAKAR
            ['prenom' => 'Marie Simone', 'nom' => 'SENGHOR', 'poste' => 'Inspecteur régional Dakar', 'direction' => 'Inspection Régionale', 'region' => 'Dakar'],
            ['prenom' => 'Moustapha', 'nom' => 'GUEYE', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Dakar'],
            ['prenom' => 'Adiouma', 'nom' => 'NGOM', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Dakar'],
            ['prenom' => 'Amadou Ba', 'nom' => 'DIAKHATE', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Dakar'],

            // INSPECTION REGIONALE DE THIES
            ['prenom' => 'Mouhamed Sileye', 'nom' => 'DIOP', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Lamine', 'nom' => 'SECK', 'poste' => 'Adjoint Administratif', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Ndèye Fatou', 'nom' => 'DIAGNE', 'poste' => 'Secrétaire', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Mamadou', 'nom' => 'DIOUF', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Abou', 'nom' => 'NIANG', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Sidate', 'nom' => 'BABOU', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Bassirou', 'nom' => 'DIENG', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Cheikhou', 'nom' => 'DIOUF', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Bidji', 'nom' => 'SOW', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],
            ['prenom' => 'Mame Cor', 'nom' => 'NDOUR', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Thiès'],

            // INSPECTION REGIONALE DE LOUGA
            ['prenom' => 'Ousmane', 'nom' => 'KA', 'poste' => 'Inspecteur par intérim', 'direction' => 'Inspection Régionale', 'region' => 'Louga'],
            ['prenom' => 'Djimis Thrope', 'nom' => 'NGOM', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Louga'],
            ['prenom' => 'Ousmane', 'nom' => 'NDIAYE', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Louga'],
            ['prenom' => 'Saliou', 'nom' => 'DIAGNE', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Louga'],

            // INSPECTION REGIONALE DE SAINT LOUIS
            ['prenom' => 'Sohibou', 'nom' => 'NIANG', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Aïssatou', 'nom' => 'HAIDARA', 'poste' => 'Adjoint Administratif', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Aida', 'nom' => 'DIEYE', 'poste' => 'Adjoint Technique', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Khady', 'nom' => 'DIOP', 'poste' => 'Secrétaire', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Fatou', 'nom' => 'YADE', 'poste' => 'Agent de bureau', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Seyni', 'nom' => 'DIAL', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],
            ['prenom' => 'Ibrahima', 'nom' => 'DIOP', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Saint-Louis'],

            // INSPECTION REGIONALE DE DIOURBEL
            ['prenom' => 'Dame', 'nom' => 'FAYE', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Ousmane', 'nom' => 'KA', 'poste' => 'Adjoint Administratif', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Sophiatou', 'nom' => 'DIENG', 'poste' => 'Secrétaire', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Khadissatou', 'nom' => 'DIAW', 'poste' => 'Agent de bureau', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Joseph', 'nom' => 'NIANG', 'poste' => 'Gérant complexe', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Demba', 'nom' => 'NDIAYE', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Abdou Khadir', 'nom' => 'SALL', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Abdoulaye', 'nom' => 'LY', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],
            ['prenom' => 'Dethié', 'nom' => 'DIENG', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Diourbel'],

            // INSPECTION REGIONALE DE KAOLACK
            ['prenom' => 'El Hadji Alioune', 'nom' => 'THIAM', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],
            ['prenom' => 'Ousmane', 'nom' => 'CAMARA', 'poste' => 'Gérant complexe', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],
            ['prenom' => 'Asse', 'nom' => 'DIOP', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],
            ['prenom' => 'Serigne Gade', 'nom' => 'DIAKHOUMPA', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],
            ['prenom' => 'Cheikh', 'nom' => 'FAYE', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],
            ['prenom' => 'Djiby', 'nom' => 'BA', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Kaolack'],

            // INSPECTION REGIONALE DE KAFFRINE
            ['prenom' => 'Aliou', 'nom' => 'DIALLO', 'poste' => 'Adjoint Administratif', 'direction' => 'Inspection Régionale', 'region' => 'Kaffrine'],
            ['prenom' => 'Khoudia', 'nom' => 'DIOP', 'poste' => 'Secrétaire', 'direction' => 'Inspection Régionale', 'region' => 'Kaffrine'],
            ['prenom' => 'Alioune', 'nom' => 'DIOUF', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Kaffrine'],
            ['prenom' => 'Boubacar', 'nom' => 'DIALLO', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Kaffrine'],

            // INSPECTION REGIONALE DE FATICK
            ['prenom' => 'Ernest Waly', 'nom' => 'DIOUF', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Fatick'],
            ['prenom' => 'Boubacar Cambel', 'nom' => 'NDIAYE', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Fatick'],
            ['prenom' => 'Sadio', 'nom' => 'DIALLO', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Fatick'],

            // INSPECTION REGIONALE DE TAMBACOUNDA
            ['prenom' => 'Soumaïla', 'nom' => 'KEITA', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Tambacounda'],
            ['prenom' => 'Cheikh Sanoussy', 'nom' => 'DIAKHABY', 'poste' => 'Gérant complexe', 'direction' => 'Inspection Régionale', 'region' => 'Tambacounda'],
            ['prenom' => 'Ibrahima', 'nom' => 'DIAGNE', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Tambacounda'],
            ['prenom' => 'Souleymane', 'nom' => 'DIOP', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Tambacounda'],
            ['prenom' => 'Issa', 'nom' => 'DIALLO', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Tambacounda'],

            // INSPECTION REGIONALE DE KEDOUGOU
            ['prenom' => 'Modou', 'nom' => 'FALL', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Kédougou'],
            ['prenom' => 'Modou Astou Djeumb', 'nom' => 'MBOW', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Kédougou'],

            // INSPECTION REGIONALE DE KOLDA
            ['prenom' => 'Ibrahima', 'nom' => 'NDIAYE', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Kolda'],
            ['prenom' => 'Mariama', 'nom' => 'DIALLO', 'poste' => 'Secrétaire', 'direction' => 'Inspection Régionale', 'region' => 'Kolda'],
            ['prenom' => 'Ousmane', 'nom' => 'BADJI', 'poste' => 'Gardien', 'direction' => 'Inspection Régionale', 'region' => 'Kolda'],
            ['prenom' => 'Mamadou', 'nom' => 'BOYE', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Kolda'],

            // INSPECTION REGIONALE DE ZIGUINCHOR
            ['prenom' => 'Insa', 'nom' => 'MBAYE', 'poste' => 'Inspecteur régional', 'direction' => 'Inspection Régionale', 'region' => 'Ziguinchor'],
            ['prenom' => 'Tidiane', 'nom' => 'BALDE', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Ziguinchor'],
            ['prenom' => 'Boubacar', 'nom' => 'DIATTA', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Ziguinchor'],

            // INSPECTION REGIONALE DE MATAM
            ['prenom' => 'Lamine', 'nom' => 'NDIAYE', 'poste' => 'Inspecteur Régional', 'direction' => 'Inspection Régionale', 'region' => 'Matam'],
            ['prenom' => 'Thierno Ciré', 'nom' => 'DIOUF', 'poste' => 'Magasinier', 'direction' => 'Inspection Régionale', 'region' => 'Matam'],
            ['prenom' => 'Oumar', 'nom' => 'DIOUF', 'poste' => 'Chauffeur', 'direction' => 'Inspection Régionale', 'region' => 'Matam'],
        ];

        foreach ($agents as $data) {
            $data['prenom_normalise'] = AgentTabaski::normaliser($data['prenom']);
            $data['nom_normalise']    = AgentTabaski::normaliser($data['nom']);
            AgentTabaski::create($data);
        }

        $this->command->info('✅ ' . count($agents) . ' agents Tabaski importés avec succès.');
    }
}
