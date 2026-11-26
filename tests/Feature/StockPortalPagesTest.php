<?php

namespace Tests\Feature;

use App\Models\InspectorWarehouseAssignment;
use App\Models\StockReportExport;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Refonte UX/UI : nouveaux tableaux de bord (DTL, Inspecteur, Magasinier,
 * Vue nationale DG) et module Rapports PDF/XLSX.
 * Vérifie le rendu, les guards et l'absence de régression d'accès.
 */
class StockPortalPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, bool $twoFactor = false): User
    {
        $user = User::create([
            'name' => 'Test ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->role = $role;
        if ($twoFactor) {
            $user->two_factor_enabled = true;
            $user->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        }
        $user->save();
        return $user->refresh();
    }

    private function makeWarehouse(string $name = 'Magasin Test', ?int $magasinierId = null): Warehouse
    {
        return Warehouse::create([
            'name' => $name, 'address' => 'Dakar', 'region' => 'Dakar',
            'capacity' => 1000, 'current_stock' => 50, 'latitude' => 14.69, 'longitude' => -17.44, 'city' => 'Dakar',
            'status' => 'active', 'is_active' => true,
        ]);
    }

    public function test_dtl_stock_dashboard_loads(): void
    {
        $dtl = $this->makeUser('dtl');
        $this->actingAs($dtl, 'dtl')->get('/dtl')->assertOk();
    }

    public function test_dtl_archives_dashboard_still_accessible(): void
    {
        $dtl = $this->makeUser('dtl');
        // /admin/dtl reste accessible directement (archives & dossiers)
        $this->actingAs($dtl, 'dtl')->get('/admin/dtl')->assertOk();
    }

    public function test_inspecteur_dashboard_loads_with_scope(): void
    {
        $inspecteur = $this->makeUser('inspecteur');
        $wh = $this->makeWarehouse();
        InspectorWarehouseAssignment::create([
            'inspector_id' => $inspecteur->id,
            'warehouse_id' => $wh->id,
            'assigned_by' => $this->makeUser('dtl')->id,
            'is_active' => true,
        ]);

        $this->actingAs($inspecteur, 'inspecteur')->get('/inspecteur')->assertOk();
    }

    public function test_inspecteur_login_page_is_dedicated(): void
    {
        $this->get('/inspecteur/login')
            ->assertOk()
            ->assertSee('Gestion du contrôle')
            ->assertSee('action="http://localhost/inspecteur/login"', false)->assertSee('Se connecter');
    }

    public function test_magasinier_dashboard_loads(): void
    {
        $mag = $this->makeUser('magasinier');
        $wh = $this->makeWarehouse();
        $mag->warehouse_id = $wh->id;
        $mag->save();

        $this->actingAs($mag, 'magasinier')->get('/magasinier')->assertOk();
    }

    public function test_dg_stock_national_loads(): void
    {
        $dg = $this->makeUser('dg', true);
        $this->actingAs($dg, 'dg')->get('/dg/stocks-national')->assertOk();
    }

    public function test_dtl_reports_index_loads(): void
    {
        $dtl = $this->makeUser('dtl');
        $this->actingAs($dtl, 'dtl')->get('/dtl/stock-reports')->assertOk();
    }

    public function test_dg_reports_index_loads(): void
    {
        $dg = $this->makeUser('dg', true);
        $this->actingAs($dg, 'dg')->get('/dg/stock-reports')->assertOk();
    }

    public function test_report_xlsx_generation_and_history(): void
    {
        $dtl = $this->makeUser('dtl');
        $wh = $this->makeWarehouse();

        $response = $this->actingAs($dtl, 'dtl')->post('/dtl/stock-reports/generate', [
            'report_type' => 'mouvements',
            'format' => 'xlsx',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('stock_report_exports', [
            'portal' => 'dtl',
            'report_type' => 'mouvements',
            'format' => 'xlsx',
        ]);
        $export = StockReportExport::latest('id')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($export->path));

        // Retéléchargement depuis l'historique
        $this->actingAs($dtl, 'dtl')
            ->get('/dtl/stock-reports/' . $export->id . '/download')
            ->assertOk();
    }

    public function test_report_pdf_generation(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->post('/dtl/stock-reports/generate', [
            'report_type' => 'operations',
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('stock_report_exports', [
            'portal' => 'dtl',
            'report_type' => 'operations',
            'format' => 'pdf',
        ]);
    }

    public function test_report_type_is_restricted_per_portal(): void
    {
        $dtl = $this->makeUser('dtl');

        // Un type réservé au catalogue DG doit être refusé côté DTL
        $this->actingAs($dtl, 'dtl')->post('/dtl/stock-reports/generate', [
            'report_type' => 'stocks-national',
            'format' => 'pdf',
        ])->assertStatus(422);
    }

    public function test_magasinier_cannot_access_dtl_reports(): void
    {
        $mag = $this->makeUser('magasinier');
        $wh = $this->makeWarehouse();
        $mag->warehouse_id = $wh->id;
        $mag->save();

        // Le middleware direction:dtl bloque l'accès au portail DTL
        $this->actingAs($mag, 'magasinier')->get('/dtl/stock-reports')
            ->assertRedirect();
    }

    public function test_inspecteur_cannot_access_dtl_reports(): void
    {
        $inspecteur = $this->makeUser('inspecteur');
        $this->actingAs($inspecteur, 'inspecteur')->get('/dtl/stock-reports')
            ->assertRedirect();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dtl')->assertRedirect();
        $this->get('/inspecteur')->assertRedirect();
        $this->get('/magasinier')->assertRedirect();
    }
}
