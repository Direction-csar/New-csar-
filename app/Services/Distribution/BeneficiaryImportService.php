<?php

namespace App\Services\Distribution;

use App\Models\DistributionBeneficiary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import de beneficiaires : lecture XLS / XLSX / CSV / liste collee, controle des
 * colonnes, validation ligne par ligne, detection des doublons (dans le fichier et
 * dans le planning), apercu AVANT import definitif.
 *
 * Aucune ecriture en base pendant l'analyse : seules les lignes valides sont
 * conservees dans un fichier temporaire, importees apres confirmation.
 */
class BeneficiaryImportService
{
    public const MAX_ROWS = 5000;
    public const MAX_FILE_KB = 5120;
    public const PENDING_TTL_MINUTES = 60;
    private const PENDING_DIR = 'distribution-imports';

    /** Alias d'en-tetes (normalises : minuscules, sans accents ni ponctuation). */
    private const ALIASES = [
        'name' => ['nom', 'nomcomplet', 'nometprenom', 'nomprenom', 'fullname', 'name', 'beneficiaire', 'nomdubeneficiaire'],
        'firstname' => ['prenom', 'prenoms', 'firstname'],
        'phone' => ['telephone', 'tel', 'phone', 'portable', 'mobile', 'numerodetelephone', 'numerotelephone', 'contact'],
        'cni' => ['cni', 'cin', 'numerocni', 'pieceidentite', 'pieceidentity', 'identite', 'ni'],
        'quantity' => ['quantite', 'quantitekg', 'kg', 'quantity', 'quantitykg', 'poids', 'poidskg'],
        'category' => ['categorie', 'category', 'type'],
        'address' => ['adresse', 'address', 'localite', 'village', 'quartier'],
    ];

    // ------------------------------------------------------------------
    // Lecture
    // ------------------------------------------------------------------

    /** @return array<int, array<int, string>> lignes brutes (premiere ligne comprise) */
    public function readFile(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            return $this->readSpreadsheet($file->getRealPath());
        }

        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->readDelimited((string) file_get_contents($file->getRealPath()));
        }

        throw new ImportException('Format non pris en charge : utilisez un fichier Excel (.xlsx, .xls) ou CSV.');
    }

    /** @return array<int, array<int, string>> */
    public function readSpreadsheet(string $path): array
    {
        try {
            $type = IOFactory::identify($path, [IOFactory::READER_XLSX, IOFactory::READER_XLS]);
            $reader = IOFactory::createReader($type);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
        } catch (\Throwable $e) {
            throw new ImportException('Fichier Excel illisible ou corrompu. Réenregistrez-le au format .xlsx puis réessayez.');
        }

        $sheet = $spreadsheet->getSheet(0);

        if ($sheet->getHighestDataRow() > self::MAX_ROWS + 1) {
            $spreadsheet->disconnectWorksheets();
            throw new ImportException('Fichier trop volumineux : maximum ' . self::MAX_ROWS . ' lignes par import.');
        }

        // Pas de calcul de formules (valeurs lues telles quelles).
        $rows = $sheet->toArray(null, false, false, false);
        $spreadsheet->disconnectWorksheets();

        return array_map(fn ($row) => array_map(fn ($c) => $this->cell($c), $row), $rows);
    }

    /** @return array<int, array<int, string>> */
    public function readDelimited(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $content), fn ($l) => trim($l) !== ''));
        if (!$lines) {
            return [];
        }

        $delimiter = ';';
        $best = -1;
        foreach ([';', "\t", ','] as $candidate) {
            $count = substr_count($lines[0], $candidate);
            if ($count > $best) {
                $best = $count;
                $delimiter = $candidate;
            }
        }

        if (count($lines) > self::MAX_ROWS + 1) {
            throw new ImportException('Liste trop volumineuse : maximum ' . self::MAX_ROWS . ' lignes par import.');
        }

        return array_map(fn ($line) => array_map(fn ($c) => $this->cell($c), str_getcsv($line, $delimiter, '"', '')), $lines);
    }

    private function cell(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return floor($value) == $value && abs($value) < 1e15
                ? sprintf('%.0f', $value)
                : rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');
        }

        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }

    // ------------------------------------------------------------------
    // Analyse (aperçu)
    // ------------------------------------------------------------------

    /**
     * @param array<int, array<int, string>> $rawRows
     * @return array{mapping: array<string,int>, header_detected: bool, warnings: string[], rows: array<int,array>, counts: array<string,int>}
     */
    public function analyze(array $rawRows, int $planningId, string $defaultCategory = 'general', float $defaultQuantity = 0.0): array
    {
        $rawRows = array_values(array_filter($rawRows, fn ($r) => count(array_filter($r, fn ($c) => $c !== '')) > 0));

        if (!$rawRows) {
            throw new ImportException('Aucune ligne à importer : le fichier ou la liste est vide.');
        }

        [$mapping, $headerDetected, $warnings] = $this->detectColumns($rawRows[0]);

        if ($headerDetected) {
            array_shift($rawRows);
        }

        if (!$rawRows) {
            throw new ImportException('Le fichier ne contient que la ligne d\'en-têtes : aucune donnée à importer.');
        }

        if (count($rawRows) > self::MAX_ROWS) {
            throw new ImportException('Trop de lignes : maximum ' . self::MAX_ROWS . ' par import.');
        }

        $rows = [];
        foreach ($rawRows as $i => $raw) {
            $rows[] = $this->normalizeRow($raw, $mapping, $i + ($headerDetected ? 2 : 1), $defaultCategory, $defaultQuantity);
        }

        $this->flagDuplicates($rows, $planningId);

        $counts = [
            'total' => count($rows),
            'ok' => count(array_filter($rows, fn ($r) => $r['status'] === 'ok')),
            'duplicate' => count(array_filter($rows, fn ($r) => $r['status'] === 'duplicate')),
            'error' => count(array_filter($rows, fn ($r) => $r['status'] === 'error')),
        ];

        return compact('mapping', 'rows', 'counts', 'warnings') + ['header_detected' => $headerDetected];
    }

    /** @return array{0: array<string,int>, 1: bool, 2: string[]} */
    private function detectColumns(array $first): array
    {
        $found = [];
        $unknown = [];

        foreach ($first as $index => $label) {
            $norm = $this->norm($label);
            if ($norm === '') {
                continue;
            }
            $key = null;
            foreach (self::ALIASES as $field => $aliases) {
                if (in_array($norm, $aliases, true)) {
                    $key = $field;
                    break;
                }
            }
            if ($key !== null && !isset($found[$key])) {
                $found[$key] = $index;
            } else {
                $unknown[] = $label;
            }
        }

        $headerDetected = isset($found['name']) || (isset($found['firstname']) && count($found) > 1);

        if (!$headerDetected) {
            return [
                ['name' => 0, 'phone' => 1, 'cni' => 2, 'quantity' => 3],
                false,
                ['Aucun en-tête reconnu : colonnes lues dans l\'ordre « nom ; téléphone ; CNI ; quantité ». Pour un contrôle fiable, ajoutez une ligne d\'en-têtes (modèle disponible).'],
            ];
        }

        $warnings = [];
        if (!isset($found['name'])) {
            // Colonnes « prenom » seule sans « nom » : refuser plutot que d'importer des noms incomplets.
            throw new ImportException('Colonne « nom » introuvable. En-têtes reconnus : nom, prénom, téléphone, CNI, quantité, catégorie, adresse.');
        }
        if ($unknown) {
            $warnings[] = 'Colonnes ignorées (non reconnues) : ' . implode(', ', array_slice($unknown, 0, 8)) . '.';
        }
        if (!isset($found['quantity'])) {
            $warnings[] = 'Pas de colonne « quantité » : la quantité par défaut du formulaire sera appliquée à chaque ligne.';
        }

        return [$found, true, $warnings];
    }

    private function normalizeRow(array $raw, array $mapping, int $line, string $defaultCategory, float $defaultQuantity): array
    {
        $get = fn (string $field) => isset($mapping[$field]) ? ($raw[$mapping[$field]] ?? '') : '';
        $messages = [];

        $name = $get('name');
        if (isset($mapping['firstname']) && ($raw[$mapping['firstname']] ?? '') !== '') {
            $name = trim($raw[$mapping['firstname']] . ' ' . $name);
        }

        $phoneRaw = $get('phone');
        $cniRaw = $get('cni');
        $qtyRaw = $get('quantity');
        $category = $get('category') ?: $defaultCategory;
        $address = $get('address');

        foreach (['nom' => $name, 'téléphone' => $phoneRaw, 'CNI' => $cniRaw, 'quantité' => $qtyRaw, 'catégorie' => $category, 'adresse' => $address] as $label => $value) {
            if ($value !== '' && $value[0] === '=') {
                $messages[] = "Formule non autorisée dans « {$label} ».";
            }
        }

        if ($name === '') {
            $messages[] = 'Nom vide.';
        } elseif (mb_strlen($name) > 255) {
            $messages[] = 'Nom trop long (255 caractères maximum).';
        }

        $phone = null;
        if ($phoneRaw !== '') {
            $compact = preg_replace('/[\s.\-()]/', '', $phoneRaw);
            if (!preg_match('/^\+?\d{7,15}$/', $compact)) {
                $messages[] = "Téléphone invalide « {$phoneRaw} » (7 à 15 chiffres attendus).";
            } else {
                $phone = $compact;
            }
        }

        $cni = null;
        if ($cniRaw !== '') {
            $cni = preg_replace('/\s+/', '', $cniRaw);
            if (mb_strlen($cni) > 100) {
                $messages[] = 'CNI trop long (100 caractères maximum).';
            }
        }

        $quantity = $defaultQuantity;
        if ($qtyRaw !== '') {
            $candidate = str_replace([' ', ','], ['', '.'], $qtyRaw);
            if (!is_numeric($candidate)) {
                $messages[] = "Quantité invalide « {$qtyRaw} ».";
                $quantity = 0.0;
            } else {
                $quantity = (float) $candidate;
            }
        }
        if (!$messages && $quantity <= 0) {
            $messages[] = 'Quantité manquante ou nulle (renseignez-la dans le fichier ou la quantité par défaut).';
        }

        if (mb_strlen($category) > 100) {
            $messages[] = 'Catégorie trop longue (100 caractères maximum).';
        }

        return [
            'line' => $line,
            'name' => $name,
            'phone' => $phone,
            'cni' => $cni,
            'quantity_kg' => round($quantity, 2),
            'category' => $category,
            'address' => $address !== '' ? $address : null,
            'status' => $messages ? 'error' : 'ok',
            'messages' => $messages,
        ];
    }

    /** Regle historique conservee : meme nom, telephone ou CNI dans le meme planning = doublon. */
    private function flagDuplicates(array &$rows, int $planningId): void
    {
        $seen = ['name' => [], 'phone' => [], 'cni' => []];

        foreach ($rows as &$row) {
            if ($row['status'] !== 'ok') {
                continue;
            }
            $keys = ['name' => Str::lower($row['name']), 'phone' => $row['phone'], 'cni' => $row['cni']];
            $hit = null;
            foreach ($keys as $type => $value) {
                if ($value !== null && $value !== '' && isset($seen[$type][$value])) {
                    $hit = [$type, $seen[$type][$value]];
                    break;
                }
            }
            if ($hit) {
                $label = ['name' => 'même nom', 'phone' => 'même téléphone', 'cni' => 'même CNI'][$hit[0]];
                $row['status'] = 'duplicate';
                $row['messages'][] = "Doublon dans le fichier ({$label} que la ligne {$hit[1]}).";
                continue;
            }
            foreach ($keys as $type => $value) {
                if ($value !== null && $value !== '') {
                    $seen[$type][$value] = $row['line'];
                }
            }
        }
        unset($row);

        $existing = $this->existingKeys($planningId, $rows);

        foreach ($rows as &$row) {
            if ($row['status'] !== 'ok') {
                continue;
            }
            $label = null;
            if (isset($existing['name'][Str::lower($row['name'])])) {
                $label = 'même nom';
            } elseif ($row['phone'] && isset($existing['phone'][$row['phone']])) {
                $label = 'même téléphone';
            } elseif ($row['cni'] && isset($existing['cni'][$row['cni']])) {
                $label = 'même CNI';
            }
            if ($label) {
                $row['status'] = 'duplicate';
                $row['messages'][] = "Déjà présent dans ce planning ({$label}).";
            }
        }
        unset($row);
    }

    /** @return array{name: array<string,true>, phone: array<string,true>, cni: array<string,true>} */
    private function existingKeys(int $planningId, array $rows): array
    {
        $out = ['name' => [], 'phone' => [], 'cni' => []];

        if (!collect($rows)->contains(fn ($r) => $r['status'] === 'ok')) {
            return $out;
        }

        // Une seule requete : la comparaison des noms (insensible a la casse) est faite en PHP,
        // ce qui evite les expressions brutes non portables entre moteurs SQL.
        DistributionBeneficiary::where('planning_id', $planningId)
            ->get(['full_name', 'phone', 'cni'])
            ->each(function ($b) use (&$out) {
                $out['name'][Str::lower((string) $b->full_name)] = true;
                if ($b->phone !== null && $b->phone !== '') {
                    $out['phone'][$b->phone] = true;
                }
                if ($b->cni !== null && $b->cni !== '') {
                    $out['cni'][$b->cni] = true;
                }
            });

        return $out;
    }

    private function norm(string $label): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($label)));
    }

    // ------------------------------------------------------------------
    // Lignes en attente de confirmation
    // ------------------------------------------------------------------

    /** Conserve les lignes VALIDES (aucune ecriture en base) et renvoie le jeton de confirmation. */
    public function storePending(int $userId, int $planningId, array $analysis): string
    {
        $this->purgeExpired();

        $token = (string) Str::uuid();
        $valid = array_values(array_filter($analysis['rows'], fn ($r) => $r['status'] === 'ok'));

        Storage::disk('local')->put(self::PENDING_DIR . "/{$token}.json", json_encode([
            'user_id' => $userId,
            'planning_id' => $planningId,
            'created_at' => now()->toIso8601String(),
            'rows' => $valid,
        ], JSON_UNESCAPED_UNICODE));

        return $token;
    }

    /** @return array{user_id:int, planning_id:int, created_at:string, rows:array}|null */
    public function loadPending(string $token, int $userId): ?array
    {
        if (!Str::isUuid($token)) {
            return null;
        }
        $path = self::PENDING_DIR . "/{$token}.json";
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        $data = json_decode(Storage::disk('local')->get($path), true);
        if (!is_array($data) || (int) ($data['user_id'] ?? 0) !== $userId
            || now()->diffInMinutes(\Carbon\Carbon::parse($data['created_at']), true) > self::PENDING_TTL_MINUTES) {
            return null;
        }

        return $data;
    }

    public function discardPending(string $token): void
    {
        if (Str::isUuid($token)) {
            Storage::disk('local')->delete(self::PENDING_DIR . "/{$token}.json");
        }
    }

    private function purgeExpired(): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files(self::PENDING_DIR) as $file) {
            if ($disk->lastModified($file) < now()->subHours(24)->timestamp) {
                $disk->delete($file);
            }
        }
    }

    // ------------------------------------------------------------------
    // Import definitif
    // ------------------------------------------------------------------

    /**
     * Re-controle les doublons au moment de la confirmation (la base a pu changer depuis
     * l'apercu), puis insere en une transaction.
     *
     * @return array{created:int, skipped:string[]}
     */
    public function commit(array $pending, int $registeredBy): array
    {
        $planningId = (int) $pending['planning_id'];
        $rows = array_map(fn ($r) => $r + [], $pending['rows']);
        $this->flagDuplicates($rows, $planningId);

        $skipped = [];
        $insert = [];
        $now = now();
        foreach ($rows as $row) {
            if ($row['status'] !== 'ok') {
                $skipped[] = "Ligne {$row['line']} ({$row['name']}) : " . implode(' ', $row['messages']);
                continue;
            }
            $insert[] = [
                'planning_id' => $planningId,
                'full_name' => $row['name'],
                'phone' => $row['phone'],
                'cni' => $row['cni'],
                'address' => $row['address'],
                'category' => $row['category'],
                'quantity_kg' => $row['quantity_kg'],
                'status' => DistributionBeneficiary::STATUS_PENDING,
                'registered_by' => $registeredBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($insert) {
            foreach (array_chunk($insert, 200) as $chunk) {
                DistributionBeneficiary::insert($chunk);
            }
        });

        return ['created' => count($insert), 'skipped' => $skipped];
    }

    // ------------------------------------------------------------------
    // Modele Excel
    // ------------------------------------------------------------------

    public function templateXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Beneficiaires');
        $sheet->fromArray([
            ['Nom complet', 'Telephone', 'CNI', 'Quantite (kg)', 'Categorie', 'Adresse'],
            ['Exemple Fictif Un', '771234567', '1234567890123', 50, 'famille', 'Quartier exemple'],
            ['Exemple Fictif Deux', '', '', 50, 'veuve', ''],
        ]);
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('B:C')->getNumberFormat()->setFormatCode('@');
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'csar_tpl_');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}
