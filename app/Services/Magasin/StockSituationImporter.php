<?php

namespace App\Services\Magasin;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Situation réelle des stocks du CSAR au 23/11/2026
 * (fichiers « SITUATION DES STOCKS CSAR » et « Situation des autres Produits KITS »).
 *
 * L'historique n'est jamais supprimé : pour chaque magasin, des mouvements de
 * régularisation (entrée / sortie) ramènent chaque produit à la quantité réelle,
 * y compris les produits absents de la situation (remis à zéro).
 */
class StockSituationImporter
{
    public const DATE = '2026-11-23';
    public const PREFIX = 'SIT-20261123';

    /** Stock de sécurité : riz en kg (vallée, indien) par région. Sacs de 50 kg. */
    private const RIZ = [
        'Dakar' => [34600, 0], 'Thiès' => [150000, 0], 'Louga' => [2400, 0], 'Saint-Louis' => [3400, 0],
        'Kaolack' => [150, 0], 'Fatick' => [150, 0], 'Kaffrine' => [0, 300], 'Tambacounda' => [0, 0],
        'Kédougou' => [10550, 50], 'Kolda' => [10200, 0], 'Sédhiou' => [0, 0], 'Ziguinchor' => [100, 0],
        'Matam' => [4050, 0], 'Diourbel' => [0, 0],
    ];

    private const FORMAT_RIZ = 50;

    /**
     * Denrées alimentaires : région => [[produit, unité, format du contenant, nombre de contenants]].
     * Farine Dakar : 173 cartons de 15 kg (2 595 kg) + 10 kg en vrac = 2 605 kg.
     */
    private const DENREES = [
        'Dakar' => [
            ['Niébé', 'kg', 5, 616], ['Sucre', 'kg', 25, 130], ['Huile', 'L', 20, 1424],
            ['Araw', 'kg', 1, 580], ['Thiakry', 'kg', 1, 380], ['Farine', 'kg', 15, 173], ['Farine', 'kg', 1, 10],
        ],
        'Thiès' => [
            ['Niébé', 'kg', 5, 144],
        ],
    ];

    /** Cibles : région => liste de [produit, unité, format, contenants]. */
    public function targets(): array
    {
        $out = [];
        foreach (self::RIZ as $region => [$vallee, $indien]) {
            $out[$region] = [];
            foreach ([['Riz Vallée', $vallee], ['Riz Indien', $indien]] as [$nom, $kg]) {
                if ($kg > 0) {
                    $out[$region][] = [$nom, 'kg', self::FORMAT_RIZ, $kg / self::FORMAT_RIZ];
                }
            }
        }
        foreach (self::DENREES as $region => $lignes) {
            $out[$region] = array_merge($out[$region] ?? [], $lignes);
        }

        return $out;
    }

    /** Magasin de la région (préférence : « Complexe » à Thiès, « Central » à Dakar). */
    public function warehouseFor(string $region): ?Warehouse
    {
        $norm = fn ($s) => Str::lower(Str::ascii((string) $s));
        $r = $norm($region);
        $candidates = Warehouse::orderBy('id')->get()->filter(
            fn ($w) => $norm($w->region) === $r || str_contains($norm($w->name), $r) || str_contains($norm($w->city), $r)
        )->values();
        if ($candidates->count() > 1) {
            $hint = ['thies' => '/complexe|cereal/', 'dakar' => '/central|magasin/'][$r] ?? null;
            if ($hint && ($pref = $candidates->first(fn ($w) => preg_match($hint, $norm($w->name))))) {
                return $pref;
            }
        }

        return $candidates->first();
    }

    /**
     * @return array<int, array{region: string, warehouse: ?string, status: string, lines: list<string>}>
     */
    public function run(bool $apply, bool $force = false, ?WarehouseStockService $stock = null): array
    {
        $stock ??= app(WarehouseStockService::class);
        $report = [];

        foreach ($this->targets() as $region => $targets) {
            $wh = $this->warehouseFor($region);
            $entry = ['region' => $region, 'warehouse' => $wh?->name, 'status' => 'ok', 'lines' => []];

            if (!$wh) {
                $entry['status'] = $targets ? 'MAGASIN INTROUVABLE' : 'aucun stock, magasin absent';
                $report[] = $entry;
                continue;
            }
            if (!$force && StockMovement::where('warehouse_id', $wh->id)->where('reference', 'like', self::PREFIX . "-W{$wh->id}-%")->exists()) {
                $entry['status'] = 'déjà importé (utiliser --force pour recalculer)';
                $report[] = $entry;
                continue;
            }

            $moves = $this->plan($wh, $targets, $stock);
            foreach ($moves as $m) {
                $entry['lines'][] = sprintf('%s %s [%s%s] : %s contenants', $m['type'] === 'entry' ? '+' : '-', $m['name'], $m['format'], $m['unit'], rtrim(rtrim(number_format($m['sacs'], 2, '.', ''), '0'), '.'));
            }
            if ($apply && $moves) {
                $this->apply($wh, $moves, $stock);
            }
            $entry['status'] = $moves ? ($apply ? 'appliqué' : 'à appliquer') : 'déjà conforme';
            $report[] = $entry;
        }

        return $report;
    }

    /** Écarts entre le stock calculé et la situation réelle. */
    private function plan(Warehouse $wh, array $targets, WarehouseStockService $stock): array
    {
        $norm = fn ($s) => Str::lower(Str::ascii((string) $s));
        $current = [];
        foreach ($stock->stockStatus($wh->id)['products'] as $p) {
            $current[$norm($p['product_name']) . '|' . (int) $p['format_kg']] = $p;
        }

        $wanted = [];
        foreach ($targets as [$name, $unit, $format, $sacs]) {
            $wanted[$norm($name) . '|' . $format] = compact('name', 'unit', 'format', 'sacs');
        }

        $moves = [];
        foreach ($wanted as $key => $w) {
            $diff = $w['sacs'] - (float) ($current[$key]['total_sacs'] ?? 0);
            if (abs($diff) > 0.0001) {
                $moves[] = ['name' => $current[$key]['product_name'] ?? $w['name'], 'unit' => $w['unit'], 'format' => $w['format'],
                    'type' => $diff > 0 ? 'entry' : 'exit', 'sacs' => abs($diff)];
            }
        }
        foreach ($current as $key => $p) {
            if (!isset($wanted[$key]) && (float) $p['total_sacs'] > 0.0001) {
                $moves[] = ['name' => $p['product_name'], 'unit' => $p['unit'] === 'L' ? 'L' : 'kg', 'format' => (int) $p['format_kg'],
                    'type' => 'exit', 'sacs' => (float) $p['total_sacs']];
            }
        }

        return $moves;
    }

    private function apply(Warehouse $wh, array $moves, WarehouseStockService $stock): void
    {
        DB::transaction(function () use ($wh, $moves, $stock) {
            Warehouse::where('id', $wh->id)->lockForUpdate()->first();
            $total = (float) $stock->balance($wh->id)['sacs'];
            $date = self::DATE;
            $userId = \App\Models\User::whereIn('role', ['super_admin', 'admin'])->orderBy('id')->value('id')
                ?? \App\Models\User::orderBy('id')->value('id');

            foreach ($moves as $i => $m) {
                $after = $total + ($m['type'] === 'entry' ? $m['sacs'] : -$m['sacs']);
                $movement = StockMovement::create([
                    'warehouse_id' => $wh->id,
                    'stock_id' => null,
                    'type' => $m['type'],
                    'quantity' => $m['sacs'],
                    'quantity_before' => $total,
                    'quantity_after' => $after,
                    'reason' => "{$m['name']} [{$m['format']}{$m['unit']}] — Situation réelle du 23/11/2026",
                    'reference' => sprintf('%s-W%d-%02d', self::PREFIX, $wh->id, $i + 1),
                    'created_by' => $userId,
                    // Situation de référence validée : ces mouvements
                    // constituent le stock de départ officiel.
                    'status' => 'verified',
                    'validated_by' => $userId,
                    'validated_at' => now(),
                ]);
                $movement->metadata = json_encode([
                    'entry_sacs' => $m['type'] === 'entry' ? $m['sacs'] : 0,
                    'entry_kg' => $m['type'] === 'entry' ? $m['sacs'] * $m['format'] : 0,
                    'exit_sacs' => $m['type'] === 'exit' ? $m['sacs'] : 0,
                    'exit_kg' => $m['type'] === 'exit' ? $m['sacs'] * $m['format'] : 0,
                    'balance_sacs' => $after,
                    'movement_date' => $date,
                    'observation' => 'Régularisation sur la situation réelle des stocks du 23/11/2026',
                    'source' => 'situation_reelle_20261123',
                ]);
                $movement->save();
                $total = $after;
            }

            Warehouse::where('id', $wh->id)->update(['current_stock' => max(0, $total)]);
        });

        foreach ($moves as $m) {
            $this->ensureProduct($m['name'], $m['unit']);
        }
    }

    private function ensureProduct(string $name, string $unit): void
    {
        $norm = fn ($s) => Str::lower(Str::ascii((string) $s));
        if (Product::get(['name'])->contains(fn ($p) => $norm($p->name) === $norm($name))) {
            return;
        }
        if ($name === 'Non spécifié') {
            return;
        }
        $attrs = ['name' => $name, 'unit' => $unit, 'category' => 'Denrées alimentaires', 'is_active' => 1];
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'type')) {
            $attrs['type'] = 'alimentaire';
        }
        Product::create($attrs);
    }
}
