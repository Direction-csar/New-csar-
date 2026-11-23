<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Inventaire LECTURE SEULE du module magasins/stock.
 * Ne modifie aucune donnée. Produit un rapport JSON + texte dans storage/app/audit-stock-<date>.json
 */
class StockAudit extends Command
{
    protected $signature = 'stock:audit {--out= : Chemin du fichier de sortie}';
    protected $description = 'Inventaire lecture seule des magasins, stocks, mouvements et comptes (aucune modification)';

    public function handle(): int
    {
        $report = [
            'generated_at' => now()->toDateTimeString(),
            'warehouses' => $this->warehouses(),
            'users' => $this->users(),
            'products' => DB::table('products')->select('id', 'name', 'unit', 'type', 'is_active', 'created_at')->orderBy('id')->get(),
            'stock_types' => DB::table('stock_types')->select('id', 'name', 'display_name', 'unit')->get(),
            'stocks' => $this->stocks(),
            'stock_levels' => $this->stockLevels(),
            'stock_movements' => $this->movements(),
            'stock_alerts' => $this->alerts(),
            'missions' => $this->missions(),
            'counts' => [
                'warehouses' => DB::table('warehouses')->count(),
                'stock_movements' => DB::table('stock_movements')->count(),
                'stocks' => DB::table('stocks')->count(),
                'users_magasinier' => DB::table('users')->where('role', 'magasinier')->count(),
                'users_inspecteur' => DB::table('users')->where('role', 'inspecteur')->count(),
            ],
        ];

        $path = $this->option('out')
            ?: storage_path('app/audit-stock-' . now()->format('Ymd-His') . '.json');
        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info("Rapport écrit : {$path}");
        $this->table(['Table', 'Lignes'], collect($report['counts'])->map(fn ($v, $k) => [$k, $v])->values()->all());

        return self::SUCCESS;
    }

    private function warehouses(): array
    {
        $byWarehouse = DB::table('users')->whereNotNull('warehouse_id')
            ->select('warehouse_id', 'name', 'email')->get()
            ->groupBy('warehouse_id')
            ->map(fn ($g) => $g->map(fn ($u) => "{$u->name} <{$u->email}>")->implode(' | '));

        return DB::table('warehouses')
            ->select('id', 'name', 'region', 'city', 'address', 'is_active', 'status',
                'current_stock', 'created_at')
            ->orderBy('id')->get()
            ->map(fn ($w) => (array) $w + ['responsables' => $byWarehouse[$w->id] ?? null])
            ->all();
    }

    private function users(): array
    {
        return DB::table('users')
            ->leftJoin('warehouses as w', 'w.id', '=', 'users.warehouse_id')
            ->whereIn('users.role', ['magasinier', 'inspecteur', 'admin', 'super_admin'])
            ->select('users.id', 'users.name', 'users.email', 'users.role', 'users.status',
                'users.is_active', 'users.last_login_at', 'users.created_at', 'w.name as warehouse_name')
            ->orderBy('users.role')->orderBy('users.id')->get()->map(fn ($r) => (array) $r)->all();
    }

    private function stocks(): array
    {
        return DB::table('stocks as s')
            ->leftJoin('warehouses as w', 'w.id', '=', 's.warehouse_id')
            ->leftJoin('stock_types as st', 'st.id', '=', 's.stock_type_id')
            ->select('s.id', 'w.name as warehouse', 'st.name as type', 'st.display_name',
                's.quantity', 's.min_quantity', 's.created_at')
            ->orderBy('s.warehouse_id')->get()->map(fn ($r) => (array) $r)->all();
    }

    private function stockLevels(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('stock_levels')) {
            return [];
        }
        return DB::table('stock_levels')->orderBy('warehouse_id')->limit(500)->get()->map(fn ($r) => (array) $r)->all();
    }

    private function movements(): array
    {
        return DB::table('stock_movements as m')
            ->leftJoin('warehouses as w', 'w.id', '=', 'm.warehouse_id')
            ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
            ->select('m.id', 'w.name as warehouse', 'm.type', 'm.produit', 'm.unite',
                'm.quantity', 'm.quantity_before', 'm.quantity_after', 'm.reason',
                'm.reference', 'm.reference_number', 'm.numero_transfert', 'm.status',
                'm.validated_by', 'm.request_mission_id', 'm.public_request_id',
                'u.name as created_by_name', 'm.date_mouvement', 'm.created_at')
            ->orderBy('m.id')->get()->map(fn ($r) => (array) $r)->all();
    }

    private function alerts(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('stock_alerts')) {
            return [];
        }
        return DB::table('stock_alerts')->orderByDesc('id')->limit(500)->get()->map(fn ($r) => (array) $r)->all();
    }

    private function missions(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('request_missions')) {
            return [];
        }
        return DB::table('request_missions')
            ->leftJoin('users as a', 'a.id', '=', 'request_missions.assigned_to')
            ->leftJoin('users as b', 'b.id', '=', 'request_missions.assigned_by')
            ->select('request_missions.id', 'request_missions.type', 'request_missions.status',
                'request_missions.instructions', 'a.name as assigne', 'b.name as assignant',
                'request_missions.reported_at', 'request_missions.completed_at', 'request_missions.created_at')
            ->orderByDesc('request_missions.id')->limit(500)->get()->map(fn ($r) => (array) $r)->all();
    }
}
