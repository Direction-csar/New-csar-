<?php

namespace App\Services\Magasin;

use App\Models\InspectorWarehouseAssignment;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Str;

/**
 * Couverture des 14 régions du Sénégal : magasins, magasiniers responsables
 * et inspecteurs affectés. Lecture seule, aucune donnée n'est créée.
 */
class InspectorCoverageService
{
    public const REGIONS = [
        'Dakar', 'Thiès', 'Diourbel', 'Fatick', 'Kaolack', 'Kaffrine', 'Louga',
        'Saint-Louis', 'Matam', 'Tambacounda', 'Kédougou', 'Kolda', 'Sédhiou', 'Ziguinchor',
    ];

    public static function normalize(?string $s): string
    {
        return Str::lower(Str::ascii(trim((string) $s)));
    }

    /** Région officielle correspondant à un libellé libre (ou null). */
    public static function canonicalRegion(?string $label): ?string
    {
        $n = self::normalize($label);
        foreach (self::REGIONS as $r) {
            if (self::normalize($r) === $n || self::normalize(str_replace('-', ' ', $r)) === str_replace('-', ' ', $n)) {
                return $r;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{region:string, warehouses:array, status:string}>
     */
    public function report(): array
    {
        $warehouses = Warehouse::where('status', 'active')->where('is_active', true)->orderBy('name')->get();
        $magasiniers = User::where('role', 'magasinier')->whereNotNull('warehouse_id')->get()->groupBy('warehouse_id');
        $assignments = InspectorWarehouseAssignment::with('inspector:id,name,email,is_active')
            ->where('is_active', true)->get()->groupBy('warehouse_id');

        $byRegion = $warehouses->groupBy(fn ($w) => self::canonicalRegion($w->region) ?? self::canonicalRegion($w->city) ?? '__autre');

        $out = [];
        foreach (array_merge(self::REGIONS, ['__autre']) as $region) {
            $list = $byRegion->get($region, collect());
            if ($region === '__autre' && $list->isEmpty()) {
                continue;
            }
            $rows = $list->map(function ($w) use ($magasiniers, $assignments) {
                $mags = $magasiniers->get($w->id, collect())->where('is_active', true);
                $insps = $assignments->get($w->id, collect())->pluck('inspector')->filter(fn ($i) => $i && $i->is_active);

                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'magasiniers' => $mags->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])->values()->all(),
                    'inspecteurs' => $insps->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])->values()->all(),
                ];
            })->values()->all();

            $out[] = [
                'region' => $region === '__autre' ? 'Hors région officielle' : $region,
                'warehouses' => $rows,
                'status' => $this->status($rows),
            ];
        }

        return $out;
    }

    private function status(array $rows): string
    {
        if (!$rows) {
            return 'AUCUN MAGASIN';
        }
        $gaps = [];
        foreach ($rows as $r) {
            if (!$r['magasiniers']) {
                $gaps[] = 'sans magasinier';
            }
            if (!$r['inspecteurs']) {
                $gaps[] = 'sans inspecteur';
            }
        }

        return $gaps ? 'INCOMPLET (' . implode(', ', array_unique($gaps)) . ')' : 'COUVERT';
    }
}
