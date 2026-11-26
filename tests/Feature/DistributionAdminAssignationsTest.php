<?php

namespace Tests\Feature;

use App\Models\DistributionPlanning;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

/**
 * Administration web : affectations multiples et independantes (distributeurs / scanners)
 * via les formulaires planning, liste des agents et statistiques par agent.
 * Donnees fictives, SQLite :memory:.
 */
class DistributionAdminAssignationsTest extends TestCase
{
    use BuildsDistributionSchema;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildDistributionSchema();
        $this->admin = $this->makeUser('Admin Assign', 'admin');
        $this->admin->forceFill(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')])->save();
    }

    private function planningPayload(array $overrides = []): array
    {
        $event = $this->makeEvent();

        return $overrides + [
            'event_id' => $event->id,
            'name' => 'Planning web test',
            'planned_quota_kg' => 500,
        ];
    }

    public function test_creation_avec_plusieurs_distributeurs_et_scanners_independants(): void
    {
        [$d1, $d2, $s1, $s2] = [
            $this->makeUser('D1', 'distributeur'), $this->makeUser('D2', 'distributeur'),
            $this->makeUser('S1', 'scanner'), $this->makeUser('S2', 'magasinier'),
        ];

        $this->actingAs($this->admin, 'admin')->post('/admin/distribution/plannings', $this->planningPayload([
            'distributor_ids' => [$d1->id, $d2->id],
            'scanner_ids' => [$s1->id, $s2->id],
        ]))->assertRedirect();

        $p = DistributionPlanning::sole();
        $this->assertEqualsCanonicalizing([$d1->id, $d2->id], $p->distributorAgents()->pluck('user_id')->all());
        $this->assertEqualsCanonicalizing([$s1->id, $s2->id], $p->scanners()->pluck('user_id')->all());
        $this->assertSame($d1->id, (int) $p->assigned_to); // compatibilite : premier distributeur
        $this->assertSame(4, $p->agents()->count());
    }

    public function test_compatibilite_avec_l_ancien_champ_assigned_to(): void
    {
        $d = $this->makeUser('D', 'distributeur');

        $this->actingAs($this->admin, 'admin')->post('/admin/distribution/plannings', $this->planningPayload([
            'assigned_to' => $d->id,
        ]))->assertRedirect();

        $p = DistributionPlanning::sole();
        $this->assertSame([$d->id], $p->distributorAgents()->pluck('user_id')->all());
        $this->assertSame(0, $p->scanners()->count());
    }

    public function test_mise_a_jour_ajoute_retire_des_agents_sans_melanger_les_types(): void
    {
        [$d1, $d2, $s1] = [$this->makeUser('D1', 'distributeur'), $this->makeUser('D2', 'distributeur'), $this->makeUser('S1', 'scanner')];
        $p = $this->makePlanning('P', ['distributors' => [$d1], 'scanners' => [$s1]]);

        $this->actingAs($this->admin, 'admin')->put("/admin/distribution/plannings/{$p->id}", $this->planningPayload([
            'event_id' => $p->event_id, 'name' => 'P',
            'status' => 'active',
            'distributor_ids' => [$d2->id],   // remplace d1 par d2
            'scanner_ids' => [$s1->id, $d1->id], // d1 devient aussi scanner (autorisation explicite)
        ]))->assertRedirect();

        $this->assertEqualsCanonicalizing([$d2->id], $p->distributorAgents()->pluck('user_id')->all());
        $this->assertEqualsCanonicalizing([$s1->id, $d1->id], $p->scanners()->pluck('user_id')->all());
        $this->assertSame($d2->id, (int) $p->fresh()->assigned_to);
    }

    public function test_la_fiche_planning_liste_les_agents_et_affiche_les_stats_par_agent(): void
    {
        $d = $this->makeUser('D', 'distributeur');
        $s = $this->makeUser('S', 'scanner');
        $p = $this->makePlanning('P', ['distributors' => [$d], 'scanners' => [$s]]);
        $b = $this->makeBeneficiary($p, 'B', 'validated');
        $t = $this->makeTicket($p, 'T-1');
        DB::table('distribution_tickets')->where('id', $t->id)->update(['issued_by' => $d->id, 'scanned_by' => $s->id]);
        DB::table('distribution_scan_attempts')->insert([
            'user_id' => $s->id, 'ticket_id' => $t->id, 'planning_id' => $p->id,
            'action' => 'verify', 'result' => 'ok', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $res = $this->actingAs($this->admin, 'admin')->get("/admin/distribution/plannings/{$p->id}")->assertOk();
        $stats = $res->viewData('agentStats');

        $this->assertTrue($stats[$d->id]['is_distributor'] && !$stats[$d->id]['is_scanner']);
        $this->assertTrue($stats[$s->id]['is_scanner'] && !$stats[$s->id]['is_distributor']);
        $this->assertSame(1, $stats[$d->id]['issued']);
        $this->assertSame(1, $stats[$s->id]['collected']);
        $this->assertSame(1, $stats[$s->id]['verified']);
        $res->assertSee('Agents distributeurs', false)->assertSee('Agents scanners', false);
    }

    public function test_generation_ticket_admin_enregistre_l_emetteur(): void
    {
        $p = $this->makePlanning('P');
        $b = $this->makeBeneficiary($p, 'B', 'validated');

        $this->actingAs($this->admin, 'admin')->post("/admin/distribution/beneficiaries/{$b->id}/generate-ticket")->assertRedirect();
        $this->assertSame($this->admin->id, (int) DistributionTicket::sole()->issued_by);
    }
}
