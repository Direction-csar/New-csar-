<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class AgentsImportSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/agents.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("Fichier non trouvé : $csvPath");
            $this->command->line("Enregistrez votre Excel en CSV (séparateur ; ) et placez-le à cet emplacement.");
            return;
        }

        $content = File::get($csvPath);
        $lines = array_filter(explode("\n", $content));

        if (empty($lines)) {
            $this->command->error("Le fichier CSV est vide.");
            return;
        }

        // Supprimer l'en-tête
        array_shift($lines);

        $inserted = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'TOTAL') || str_starts_with($line, 'Notes') || str_starts_with($line, '"') || str_starts_with($line, '1)')) {
                continue;
            }

            $cols = str_getcsv($line, ';');

            // Ignorer les lignes sans numéro d'agent valide
            if (empty($cols[0]) || !is_numeric(str_replace(' ', '', $cols[0]))) {
                continue;
            }

            $data = $this->mapRow($cols);

            if (!$data) {
                $skipped++;
                continue;
            }

            // Vérifier si le matricule existe déjà
            if (Personnel::where('matricule', $data['matricule'])->exists()) {
                $this->command->warn("Matricule existant, ignoré : {$data['matricule']}");
                $skipped++;
                continue;
            }

            Personnel::create($data);
            $inserted++;
        }

        $this->command->info("Agents insérés : $inserted");
        $this->command->info("Agents ignorés : $skipped");
    }

    private function mapRow(array $cols): ?array
    {
        if (count($cols) < 17) {
            return null;
        }

        $directionRaw = trim($cols[1] ?? '');
        $prenom = trim($cols[2] ?? '');
        $nom = trim($cols[3] ?? '');
        $matricule = trim($cols[4] ?? '');
        $poste = trim($cols[5] ?? '');
        $diplomeDetail = trim($cols[6] ?? '');
        $categorie = trim($cols[7] ?? '');
        $statutExcel = trim($cols[8] ?? '');
        $adresse = trim($cols[9] ?? '');
        $cin = trim($cols[10] ?? '');
        $dateNaissanceRaw = trim($cols[11] ?? '');
        $filiation = trim($cols[12] ?? '');
        $dateEmbaucheRaw = trim($cols[13] ?? '');
        $situationFamille = trim($cols[14] ?? '');
        $nbEnfants = (int) ($cols[15] ?? 0);

        if ($matricule === '' || $prenom === '' || $nom === '') {
            return null;
        }

        [$dateNaissance, $lieuNaissance] = $this->parseDateLieu($dateNaissanceRaw);
        $dateEmbauche = $this->parseDate($dateEmbaucheRaw);

        $prenomsNom = trim("$prenom $nom");
        $sexe = $this->detectSexe($prenom, $situationFamille);
        $situationMatrimoniale = $this->mapSituation($situationFamille);
        $trancheAge = $this->calculateTrancheAge($dateNaissance);

        return [
            'prenoms_nom' => $prenomsNom,
            'date_naissance' => $dateNaissance,
            'lieu_naissance' => $lieuNaissance ?: 'Non renseigné',
            'tranche_age' => $trancheAge,
            'nationalite' => 'Sénégalaise',
            'numero_cni' => $cin ?: 'Non renseigné',
            'sexe' => $sexe,
            'situation_matrimoniale' => $situationMatrimoniale,
            'nombre_enfants' => $nbEnfants,
            'contact_telephonique' => 'Non renseigné',
            'email' => $this->generateEmail($prenom, $nom),
            'groupe_sanguin' => 'O+',
            'adresse_complete' => $adresse ?: 'Non renseigné',
            'matricule' => $matricule,
            'date_recrutement_csar' => $dateEmbauche,
            'date_prise_service_csar' => $dateEmbauche,
            'statut' => 'Contractuel',
            'poste_actuel' => $poste ?: 'Non renseigné',
            'direction_service' => $this->mapDirection($directionRaw),
            'localisation_region' => $this->mapRegion($directionRaw, $adresse),
            'dernier_poste_avant_csar' => null,
            'formations_professionnelles' => $diplomeDetail ?: null,
            'diplome_academique' => $this->mapDiplome($diplomeDetail),
            'autres_diplomes_certifications' => $categorie ? "Catégorie convention CSAR : $categorie" : null,
            'logiciels_maitrises' => null,
            'langues_parlees' => json_encode(['Français']),
            'autres_aptitudes' => null,
            'aspirations_professionnelles' => null,
            'interet_nouvelles_responsabilites' => 'Neutre',
            'photo_personnelle' => null,
            'taille_vetements' => 'M',
            'contact_urgence_nom' => 'Non renseigné',
            'contact_urgence_telephone' => 'Non renseigné',
            'contact_urgence_lien_parente' => 'Non renseigné',
            'observations_personnelles' => $filiation ? "Filiation : $filiation" : null,
            'statut_validation' => 'Valide',
        ];
    }

    private function parseDateLieu(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+à\s+(.+)/i', $raw, $matches)) {
            return [$this->parseDate($matches[1]), trim($matches[2])];
        }
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $raw, $matches)) {
            return [$this->parseDate($matches[1]), 'Non renseigné'];
        }
        return [null, 'Non renseigné'];
    }

    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $raw, $matches)) {
            [$d, $m, $y] = [$matches[1], $matches[2], $matches[3]];
            if (checkdate((int) $m, (int) $d, (int) $y)) {
                return "$y-$m-$d";
            }
        }
        return null;
    }

    private function mapDirection(string $raw): string
    {
        $raw = mb_strtoupper($raw);

        return match (true) {
            str_contains($raw, 'DIRECTION GENERALE') => 'Direction Generale',
            str_contains($raw, 'SECRETAIRE GENERAL') || str_contains($raw, 'POOL SECRETARIAT') => 'Secretariat general',
            str_contains($raw, 'ADMINISTRATIVE') || str_contains($raw, 'FINANCIERE') => 'DFC',
            str_contains($raw, 'RESSOURCES HUMAINES') => 'DRH',
            str_contains($raw, 'DISAR') || str_contains($raw, 'INTERVENTIONS') || str_contains($raw, 'SECURITE ALIMENTAIRE') => 'DSAR',
            str_contains($raw, 'INFORMATIQUE') => 'CI',
            str_contains($raw, 'AUDIT INTERNE') => 'CIA',
            str_contains($raw, 'PASSATION') => 'Autre',
            str_contains($raw, 'COMMUNICATION') => 'Autre',
            str_contains($raw, 'ETUDES') || str_contains($raw, 'PLANIFICATION') || str_contains($raw, 'SUIVI') => 'Autre',
            str_contains($raw, 'INSPECTION REGIONALE') => 'IR',
            default => 'Autre',
        };
    }

    private function mapRegion(string $direction, string $adresse): ?string
    {
        $direction = mb_strtoupper($direction);
        $adresse = mb_strtoupper($adresse);

        $regions = [
            'DAKAR' => 'Dakar',
            'THIES' => 'Thies',
            'TAMBACOUNDA' => 'Tambacounda',
            'MATAM' => 'Matam',
            'KAOLACK' => 'Kaolack',
            'KEDOUGOU' => 'Kedougou',
            'LOUGA' => 'Louga',
            'SAINT-LOUIS' => 'Saint-Louis',
            'SAINT LOUIS' => 'Saint-Louis',
            'DIOURBEL' => 'Diourbel',
            'FATICK' => 'Fatick',
            'KAFFRINE' => 'Kaffrine',
            'KOLDA' => 'Kolda Sedhiou',
            'SEDHIOU' => 'Kolda Sedhiou',
            'ZIGUINCHOR' => 'Ziguinchor',
        ];

        foreach ($regions as $key => $value) {
            if (str_contains($direction, $key) || str_contains($adresse, $key)) {
                return $value;
            }
        }

        // Directions centrales basées à Dakar
        if (in_array($this->mapDirection($direction), ['Direction Generale', 'Secretariat general', 'DFC', 'DRH', 'DSAR', 'CI', 'CIA', 'Autre'])) {
            return 'Dakar';
        }

        return null;
    }

    private function mapDiplome(string $raw): string
    {
        $raw = mb_strtoupper($raw);

        return match (true) {
            str_contains($raw, 'DOCTORAT') => 'Doctorat',
            str_contains($raw, 'MASTER') => 'Master',
            str_contains($raw, 'DESS') => 'DESS',
            str_contains($raw, 'MAITRISE') => 'Maitrise',
            str_contains($raw, 'LICENCE') => 'Licence',
            str_contains($raw, 'DEUG') => 'DEUG',
            str_contains($raw, 'BACCALAUREAT') || str_contains($raw, 'BACCALAURÉAT') || str_contains($raw, 'BAC ') => 'Baccalaureat',
            str_contains($raw, 'BFEM') || str_contains($raw, 'BREVET DE FIN D\'ETUDES MOYENNES') => 'BFEM',
            str_contains($raw, 'CFEE') || str_contains($raw, 'CERTIFICAT DE FIN D\'ETUDE ELEMENTAIRE') => 'CFEE',
            str_contains($raw, 'PERMIS') || str_contains($raw, 'GARDIEN') || str_contains($raw, 'SANS DIPLOME') => 'Sans diplome',
            default => 'Autre',
        };
    }

    private function mapSituation(string $raw): string
    {
        $raw = mb_strtolower($raw);

        return match (true) {
            str_contains($raw, 'marié') || str_contains($raw, 'marie') => 'Marie',
            str_contains($raw, 'célibataire') || str_contains($raw, 'celibataire') => 'Celibataire',
            str_contains($raw, 'divorcé') || str_contains($raw, 'divorce') => 'Divorce',
            str_contains($raw, 'veuf') => 'Veuf',
            str_contains($raw, 'veuve') => 'Veuve',
            default => 'Celibataire',
        };
    }

    private function detectSexe(string $prenom, string $situation): string
    {
        $prenomLower = mb_strtolower(trim($prenom));
        $situationLower = mb_strtolower($situation);

        $femaleNames = [
            'awa', 'bineta', 'coumba', 'khoudia', 'ndéye', 'ndeye', 'ndèye', 'fatou', 'mame', 'marie',
            'souadou', 'khady', 'aïssatou', 'aissatou', 'sadio', 'sophiatou', 'khadissatou', 'adama',
            'maty', 'yandé', 'dieynaba', 'marème', 'maryame', 'mereme', 'adji', 'adicke', 'astou', 'mariama',
        ];

        if (str_contains($situationLower, 'mariée') || str_contains($situationLower, 'veuve')) {
            return 'Feminin';
        }

        foreach ($femaleNames as $name) {
            if (str_contains($prenomLower, $name)) {
                return 'Feminin';
            }
        }

        return 'Masculin';
    }

    private function calculateTrancheAge(?string $dateNaissance): string
    {
        if (!$dateNaissance) {
            return '26-35';
        }

        $age = Carbon::parse($dateNaissance)->age;

        return match (true) {
            $age <= 25 => '18-25',
            $age <= 35 => '26-35',
            $age <= 45 => '36-45',
            $age <= 55 => '46-55',
            $age <= 60 => '56-60',
            default => '56-60',
        };
    }

    private function generateEmail(string $prenom, string $nom): string
    {
        $clean = function ($s) {
            return preg_replace('/[^a-z0-9]/', '', mb_strtolower($s));
        };

        return $clean($prenom) . '.' . $clean($nom) . '@csar.sn';
    }
}
