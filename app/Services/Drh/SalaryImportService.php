<?php

namespace App\Services\Drh;

use App\Models\Personnel;
use App\Models\RhSalaryImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Actualisation des rémunérations à partir du fichier TXT officiel :
 * 1) preview() enregistre une prévisualisation (aucune fiche modifiée) ;
 * 2) apply() n'écrit que les champs salariaux des lignes validées, en transaction.
 */
class SalaryImportService
{
    /** Champ du fichier => colonne de la table personnel. */
    public const FIELD_MAP = [
        'poste' => 'poste_actuel',
        'categorie' => 'categorie',
        'salaire_base' => 'salaire_base',
        'sursalaire' => 'sursalaire',
        'indemnite_fonction' => 'indemnite_fonction',
        'indemnite_transport' => 'indemnite_transport',
        'salaire_brut' => 'salaire_brut',
        'net_a_payer' => 'net_a_payer',
    ];

    private const MONEY_COLUMNS = ['salaire_base', 'sursalaire', 'indemnite_fonction', 'indemnite_transport', 'salaire_brut', 'net_a_payer'];

    /** Avertissements qui empêchent l'écriture d'une ligne. */
    private const BLOCKING = ['nom_different', 'conflit_feuilles'];

    public function __construct(private SalaryFileParser $parser)
    {
    }

    public function preview(string $content, string $filename, ?int $userId): RhSalaryImport
    {
        $parsed = $this->parser->parse($content);
        $agents = $parsed['agents'];

        $fileMatricules = array_count_values(array_filter(array_column($agents, 'matricule')));
        $personnels = Personnel::query()
            ->get(['id', 'matricule', 'prenoms_nom', ...array_values(self::FIELD_MAP)])
            ->keyBy(fn ($p) => self::normMatricule($p->matricule));

        $rows = [];
        foreach ($agents as $a) {
            $row = [
                'ligne' => $a['ligne'],
                'section' => $a['section'],
                'matricule' => $a['matricule'],
                'nom_fichier' => trim($a['prenoms'] . ' ' . $a['nom']),
                'personnel_id' => null,
                'nom_base' => null,
                'old' => [],
                'new' => [],
                'changes' => [],
                'warnings' => [],
            ];

            foreach (self::FIELD_MAP as $src => $col) {
                $v = $a[$src];
                // Indemnité absente du fichier = aucune indemnité (le brut le confirme).
                $row['new'][$col] = in_array($col, self::MONEY_COLUMNS, true) ? (float) ($v ?? 0) : ($v === '' ? null : $v);
            }

            $brutCalc = ($a['salaire_base'] ?? 0) + ($a['sursalaire'] ?? 0) + ($a['indemnite_fonction'] ?? 0);
            if (abs($brutCalc - (float) $a['salaire_brut']) > 1) {
                $row['warnings'][] = 'brut_incoherent';
            }
            if (in_array($a['matricule'], $parsed['conflicts'], true)) {
                $row['warnings'][] = 'conflit_feuilles';
            }

            if ($a['matricule'] === '') {
                $row['status'] = 'sans_matricule';
            } elseif (($fileMatricules[$a['matricule']] ?? 0) > 1) {
                $row['status'] = 'doublon_fichier';
            } elseif (!$p = $personnels->get(self::normMatricule($a['matricule']))) {
                $row['status'] = 'non_trouve';
            } else {
                $row['personnel_id'] = $p->id;
                $row['nom_base'] = $p->prenoms_nom;
                foreach (self::FIELD_MAP as $col) {
                    $row['old'][$col] = in_array($col, self::MONEY_COLUMNS, true)
                        ? ($p->$col === null ? null : (float) $p->$col)
                        : $p->$col;
                    if (!self::same($row['old'][$col], $row['new'][$col])) {
                        $row['changes'][] = $col;
                    }
                }
                if (!self::sameName($a['nom'], $p->prenoms_nom)) {
                    $row['warnings'][] = 'nom_different';
                }
                if ($row['old']['salaire_brut'] !== null && $row['new']['salaire_brut'] < $row['old']['salaire_brut']) {
                    $row['warnings'][] = 'baisse_brut';
                }
                $row['status'] = $row['changes'] === [] ? 'inchange' : 'modifie';
            }

            $row['applicable'] = $row['status'] === 'modifie' && array_intersect($row['warnings'], self::BLOCKING) === [];
            $rows[] = $row;
        }

        $matched = array_filter(array_column($rows, 'personnel_id'));
        $summary = [
            'sheet' => $parsed['sheet'],
            'ignored_sheets' => $parsed['ignored_sheets'],
            'total' => count($rows),
            'by_status' => array_count_values(array_column($rows, 'status')),
            'applicable' => count(array_filter($rows, fn ($r) => $r['applicable'])),
            'blocked' => count(array_filter($rows, fn ($r) => $r['status'] === 'modifie' && !$r['applicable'])),
            'warnings' => array_count_values(array_merge(...array_column($rows, 'warnings')) ?: []),
            'absent_du_fichier' => Personnel::query()->whereNotIn('id', $matched ?: [0])->count(),
            'masse_brute_avant' => array_sum(array_map(fn ($r) => $r['old']['salaire_brut'] ?? 0, $rows)),
            'masse_brute_apres' => array_sum(array_map(fn ($r) => $r['personnel_id'] ? $r['new']['salaire_brut'] : 0, $rows)),
        ];

        return RhSalaryImport::create([
            'filename' => Str::limit(basename($filename), 180, ''),
            'sha256' => hash('sha256', $content),
            'status' => 'preview',
            'summary' => $summary,
            'rows' => $rows,
            'created_by' => $userId,
        ]);
    }

    /**
     * @return array{updated: int, skipped: list<string>}
     */
    public function apply(RhSalaryImport $import, ?int $userId): array
    {
        return DB::transaction(function () use ($import, $userId) {
            $import = RhSalaryImport::query()->lockForUpdate()->findOrFail($import->id);
            if ($import->isApplied()) {
                throw new RuntimeException('Cette actualisation a déjà été enregistrée.');
            }

            $updated = 0;
            $skipped = [];
            $rows = $import->rows;
            foreach ($rows as $i => $row) {
                if (!$row['applicable']) {
                    continue;
                }
                $p = Personnel::query()->lockForUpdate()->find($row['personnel_id']);
                if (!$p) {
                    $skipped[] = $row['matricule'] . ' : fiche supprimée depuis la prévisualisation';
                    continue;
                }
                // La fiche a changé depuis la prévisualisation : on ne l'écrase pas.
                foreach (self::FIELD_MAP as $col) {
                    $current = in_array($col, self::MONEY_COLUMNS, true) ? ($p->$col === null ? null : (float) $p->$col) : $p->$col;
                    if (!self::same($current, $row['old'][$col] ?? null)) {
                        $skipped[] = $row['matricule'] . ' : fiche modifiée depuis la prévisualisation';
                        continue 2;
                    }
                }
                $p->forceFill(array_intersect_key($row['new'], array_flip($row['changes'])))->save();
                $rows[$i]['applied'] = true;
                $updated++;
            }

            $import->forceFill([
                'status' => 'applied',
                'rows' => $rows,
                'applied_by' => $userId,
                'applied_at' => now(),
                'summary' => array_merge($import->summary ?? [], ['updated' => $updated, 'skipped' => $skipped]),
            ])->save();

            return ['updated' => $updated, 'skipped' => $skipped];
        });
    }

    public static function normMatricule(?string $m): string
    {
        $m = preg_replace('/\s+/', '', (string) $m);

        return ltrim($m, '0') ?: $m;
    }

    private static function same(mixed $a, mixed $b): bool
    {
        $empty = fn ($v) => $v === null || $v === '';
        if ($empty($a) || $empty($b)) {
            // Une indemnité vide et une indemnité à 0 sont équivalentes.
            $other = $empty($a) ? $b : $a;

            return $empty($other) || (is_numeric($other) && (float) $other == 0.0);
        }
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.005;
        }

        return trim((string) $a) === trim((string) $b);
    }

    private static function sameName(string $nomFichier, ?string $nomBase): bool
    {
        $norm = fn ($s) => Str::of(Str::ascii((string) $s))->upper()->replaceMatches('/[^A-Z ]/', ' ')->squish()->value();
        $nom = $norm($nomFichier);

        return $nom === '' || str_contains(' ' . $norm($nomBase) . ' ', ' ' . $nom . ' ');
    }
}
