<?php

namespace App\Services\Drh;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Lit l'export TXT du « Tableau comparatif des rémunérations » (une feuille
 * Excel par section « FEUILLE : '...' », un bloc « Ligne Excel N » par agent,
 * puis des lignes « Champ : valeur »).
 *
 * Seuls les champs utiles à l'actualisation salariale sont conservés : les
 * données personnelles (CIN, adresse, filiation...) ne sortent jamais du parseur.
 */
class SalaryFileParser
{
    /** Clés normalisées retenues pour chaque agent. */
    public const FIELDS = [
        'matricule', 'prenoms', 'nom', 'poste', 'categorie',
        'salaire_base', 'sursalaire', 'indemnite_fonction', 'indemnite_transport', 'salaire_brut', 'net_a_payer',
    ];

    private const MONEY = ['salaire_base', 'sursalaire', 'indemnite_fonction', 'indemnite_transport', 'salaire_brut', 'net_a_payer'];

    /**
     * @return array{sheet: ?string, agents: list<array<string, mixed>>, ignored_sheets: list<string>, conflicts: list<string>}
     */
    public function parse(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }

        $sheets = [];
        $sheet = '_';
        $section = null;
        $block = null;
        $flush = function () use (&$block, &$sheets, &$sheet) {
            if ($block !== null) {
                $sheets[$sheet][] = $block;
                $block = null;
            }
        };

        foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
            if (preg_match("/^FEUILLE\s*:\s*'(.+?)'/u", $line, $m)) {
                $flush();
                $sheet = $m[1];
                $section = null;
            } elseif (preg_match('/^###\s*(.+)$/u', $line, $m)) {
                $flush();
                $section = trim($m[1]);
            } elseif (preg_match('/^Ligne Excel\s+(\d+)/u', $line, $m)) {
                $flush();
                $block = ['ligne' => (int) $m[1], 'section' => $section];
            } elseif ($block !== null && preg_match('/^\s+(.+?)\s*:\s*(.*)$/u', $line, $m)) {
                $key = $this->mapKey($m[1]);
                if ($key !== null && !array_key_exists($key, $block)) {
                    $block[$key] = trim($m[2]);
                }
            }
        }
        $flush();

        // Feuilles portant des salaires ; la première fait foi, les suivantes sont comparées.
        $salarySheets = array_filter($sheets, fn ($rows) => collect($rows)->contains(fn ($r) => isset($r['salaire_brut'])));
        if ($salarySheets === []) {
            throw new InvalidArgumentException('Aucune feuille de rémunérations reconnue dans le fichier.');
        }

        $primaryName = array_key_first($salarySheets);
        $agents = $this->normalizeRows($salarySheets[$primaryName]);

        $conflicts = [];
        foreach (array_slice($salarySheets, 1, null, true) as $name => $rows) {
            $byMat = collect($this->normalizeRows($rows))->filter(fn ($r) => $r['matricule'] !== '')->keyBy('matricule');
            foreach ($agents as $a) {
                $o = $byMat->get($a['matricule']);
                if ($a['matricule'] !== '' && $o && array_intersect_key($o, array_flip(self::MONEY)) != array_intersect_key($a, array_flip(self::MONEY))) {
                    $conflicts[] = $a['matricule'];
                }
            }
        }

        return [
            'sheet' => $primaryName,
            'agents' => $agents,
            'ignored_sheets' => array_values(array_diff(array_keys($sheets), [$primaryName])),
            'conflicts' => array_values(array_unique($conflicts)),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function normalizeRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            if (Str::upper(trim($r['prenoms'] ?? '')) === 'TOTAL' || !isset($r['salaire_brut'])) {
                continue;
            }
            $row = ['ligne' => $r['ligne'], 'section' => $r['section']];
            foreach (self::FIELDS as $f) {
                $v = trim((string) ($r[$f] ?? ''));
                $row[$f] = in_array($f, self::MONEY, true) ? self::money($v) : $v;
            }
            $out[] = $row;
        }

        return $out;
    }

    private function mapKey(string $raw): ?string
    {
        $k = Str::of(Str::ascii($raw))->lower()->squish()->value();

        return match (true) {
            str_starts_with($k, 'matricule') => 'matricule',
            str_starts_with($k, 'prenom') => 'prenoms',
            $k === 'nom' => 'nom',
            str_starts_with($k, 'poste') => 'poste',
            str_contains($k, 'categorie convention') || $k === 'categorie' => 'categorie',
            str_starts_with($k, 'salaire brut') => 'salaire_brut',
            str_starts_with($k, 'salaire categoriel') || str_starts_with($k, 'salaire de base') => 'salaire_base',
            str_starts_with($k, 'sursalaire') => 'sursalaire',
            str_starts_with($k, 'indemnite de fonction') => 'indemnite_fonction',
            // La colonne 29 (transport non imposable) duplique la colonne 20.
            str_starts_with($k, 'indemnite de transport') && !str_contains($k, '29') => 'indemnite_transport',
            $k === 'nap' || str_starts_with($k, 'net a payer') => 'net_a_payer',
            default => null,
        };
    }

    public static function money(?string $raw): ?float
    {
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '-') {
            return null;
        }
        $clean = str_replace([' ', "\u{00A0}", "\u{202F}", 'FCFA', 'CFA', 'F'], '', $raw);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }
}
