<?php

namespace Tests\Feature;

use App\Models\DistributionPlanning;
use App\Models\DistributionPlanningAgent;
use App\Models\SimCollector;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

/**
 * Affectations multiples et independantes (distributeurs / scanners) et
 * securisation des autorisations API. Donnees fictives, SQLite :memory:.
 */
class DistributionAssignmentsTest extends TestCase
{
    use BuildsDistributionSchema;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildDistributionSchema();
        $this->admin = $this->makeUser('Admin Test', 'admin');
    }

    /** Requete avec jeton Bearer seul : on oublie les gardes (le login precedent a ouvert une session web dans cette instance, que Sanctum preferrerait au jeton ; les applications mobiles n envoient aucun cookie). */
    private function asToken(string $token)
    {
        $this->flushSession();
        $this->app["auth"]->forgetGuards();

        return $this->withToken($token);
    }

    private function ids($response): array
    {
        return collect($response->json('data'))->pluck('id')->sort()->values()->all();
    }

    // ---------- Migration : reprise de assigned_to ----------

    public function test_migration_reprend_chaque_assigned_to_sans_doublon_et_sans_toucher_la_colonne(): void
    {
        $a = $this->makeUser('Agent A', 'distributeur');
        $b = $this->makeUser('Agent B', 'distributeur');
        $p1 = $this->makePlanning('P1');
        $p2 = $this->makePlanning('P2');
        $p3 = $this->makePlanning('P3-sans-agent');
        DistributionPlanning::whereKey($p1->id)->update(['assigned_to' => $a->id]);
        DistributionPlanning::whereKey($p2->id)->update(['assigned_to' => $b->id]);
        DB::table('distribution_planning_agents')->delete();

        $migration = require base_path('database/migrations/2026_10_30_000001_create_distribution_planning_agents_table.php');
        $migration->up();
        $migration->up(); // idempotente

        $rows = DB::table('distribution_planning_agents')->orderBy('planning_id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame([[$p1->id, $a->id, 'distributor'], [$p2->id, $b->id, 'distributor']],
            $rows->map(fn ($r) => [$r->planning_id, $r->user_id, $r->type])->all());
        $this->assertSame($a->id, (int) DistributionPlanning::find($p1->id)->assigned_to);
        $this->assertNull(DistributionPlanning::find($p3->id)->assigned_to);
    }

    // ---------- Plusieurs distributeurs ----------

    public function test_plusieurs_distributeurs_voient_le_meme_planning_et_les_autres_non(): void
    {
        $d1 = $this->makeUser('Dist 1', 'distributeur');
        $d2 = $this->makeUser('Dist 2', 'distributeur');
        $d3 = $this->makeUser('Dist 3', 'distributeur');
        $outsider = $this->makeUser('Dist 4', 'distributeur');
        $p = $this->makePlanning('Commun', ['distributors' => [$d1, $d2, $d3]]);
        $this->makeBeneficiary($p, 'Fatou Ba');

        foreach ([$d1, $d2, $d3] as $d) {
            Sanctum::actingAs($d);
            $this->assertSame([$p->id], $this->ids($this->getJson('/mobile/v1/distribution/my-plannings')));
            $this->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertOk();
        }

        Sanctum::actingAs($outsider);
        $this->assertSame([], $this->ids($this->getJson('/mobile/v1/distribution/my-plannings')));
        $this->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertStatus(404);
    }

    public function test_distributeur_ne_voit_pas_les_plannings_d_un_autre_distributeur(): void
    {
        $d1 = $this->makeUser('Dist 1', 'distributeur');
        $d2 = $this->makeUser('Dist 2', 'distributeur');
        $p1 = $this->makePlanning('P1', ['distributors' => [$d1]]);
        $p2 = $this->makePlanning('P2', ['distributors' => [$d2]]);
        $b2 = $this->makeBeneficiary($p2, 'Autre planning', 'validated');

        Sanctum::actingAs($d1);
        $this->assertSame([$p1->id], $this->ids($this->getJson('/mobile/v1/distribution/my-plannings')));
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b2->id}/generate-ticket")->assertStatus(404);
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b2->id}/validate")->assertStatus(404);
    }

    // ---------- Plusieurs scanners ----------

    public function test_plusieurs_scanners_voient_le_planning_et_leurs_tickets(): void
    {
        $s1 = $this->makeUser('Scan 1', 'scanner');
        $s2 = $this->makeUser('Scan 2', 'magasinier');
        $s3 = $this->makeUser('Scan 3', 'agent'); // autorisation explicite d'un autre agent
        $p = $this->makePlanning('Controle', ['scanners' => [$s1, $s2, $s3]]);
        $this->makeTicket($p, 'T-1');

        foreach ([$s1, $s2, $s3] as $s) {
            Sanctum::actingAs($s);
            $this->assertSame([$p->id], $this->ids($this->getJson('/mobile/v1/distribution/scan/plannings')));
            $res = $this->getJson("/mobile/v1/distribution/scan/plannings/{$p->id}/tickets")->assertOk();
            $this->assertSame('T-1', $res->json('data.0.ticket_code'));
        }
    }

    // ---------- Independance des deux affectations ----------

    public function test_distributeur_seul_ne_peut_ni_scanner_ni_confirmer_la_remise(): void
    {
        $d = $this->makeUser('Dist seul', 'distributeur');
        $p = $this->makePlanning('P', ['distributors' => [$d]]);
        $t = $this->makeTicket($p, 'T-2');

        Sanctum::actingAs($d);
        $this->assertSame([], $this->ids($this->getJson('/mobile/v1/distribution/scan/plannings')));
        $this->getJson("/mobile/v1/distribution/scan/plannings/{$p->id}/tickets")->assertStatus(404);
        $this->postJson("/mobile/v1/distribution/tickets/{$t->qr_token}/verify")->assertStatus(403);
        $this->postJson("/mobile/v1/distribution/tickets/{$t->qr_token}/collect")->assertStatus(403);
        $this->assertSame('issued', $t->fresh()->status);
    }

    public function test_scanner_seul_ne_peut_ni_distribuer_ni_voir_les_beneficiaires(): void
    {
        $s = $this->makeUser('Scan seul', 'scanner');
        $p = $this->makePlanning('P', ['scanners' => [$s]]);
        $b = $this->makeBeneficiary($p, 'B', 'validated');

        Sanctum::actingAs($s);
        $this->assertSame([], $this->ids($this->getJson('/mobile/v1/distribution/my-plannings')));
        $this->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertStatus(404);
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket")->assertStatus(404);
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/validate")->assertStatus(404);
    }

    public function test_agent_avec_les_deux_affectations_peut_distribuer_et_scanner(): void
    {
        $u = $this->makeUser('Polyvalent', 'agent');
        $p = $this->makePlanning('P', ['distributors' => [$u], 'scanners' => [$u]]);
        $b = $this->makeBeneficiary($p, 'B', 'validated');

        Sanctum::actingAs($u);
        $ticket = $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket")->assertOk()->json('data');
        $this->postJson("/mobile/v1/distribution/tickets/{$ticket['qr_token']}/verify")->assertOk();
        $this->postJson("/mobile/v1/distribution/tickets/{$ticket['qr_token']}/collect")->assertOk();
    }

    public function test_synchroniser_un_type_ne_touche_pas_l_autre_et_realigne_assigned_to(): void
    {
        $d1 = $this->makeUser('D1', 'distributeur');
        $d2 = $this->makeUser('D2', 'distributeur');
        $s1 = $this->makeUser('S1', 'scanner');
        $p = $this->makePlanning('P', ['distributors' => [$d1, $d2], 'scanners' => [$s1]]);
        $this->assertSame($d1->id, (int) $p->assigned_to);

        $p->syncAgents('scanner', []);
        $this->assertSame(2, $p->distributors()->count());
        $this->assertSame(0, $p->scanners()->count());

        $p->syncAgents('distributor', [$d2->id]);
        $this->assertSame($d2->id, (int) $p->fresh()->assigned_to);
        $p->syncAgents('distributor', []);
        $this->assertNull($p->fresh()->assigned_to);
        $this->assertSame(0, DistributionPlanningAgent::where('planning_id', $p->id)->count());
    }

    // ---------- Planning sans agent ----------

    public function test_planning_sans_agent_est_inaccessible_a_tous_sauf_a_l_admin(): void
    {
        $p = $this->makePlanning('Orphelin');
        $b = $this->makeBeneficiary($p, 'B', 'pending');
        $t = $this->makeTicket($p, 'T-3');

        foreach (['drh', 'distributeur', 'scanner', 'magasinier', 'agent'] as $role) {
            $u = $this->makeUser('User ' . $role, $role);
            Sanctum::actingAs($u);
            $this->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertStatus(404);
            $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/validate")->assertStatus(404);
            $this->postJson("/mobile/v1/distribution/tickets/{$t->qr_token}/collect")->assertStatus(403);
            $this->assertSame([], $this->ids($this->getJson('/mobile/v1/distribution/my-plannings')));
        }

        Sanctum::actingAs($this->admin);
        $this->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertOk();
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/validate")->assertOk();
        $this->postJson("/mobile/v1/distribution/tickets/{$t->qr_token}/collect")->assertOk();
    }

    // ---------- Jetons / comptes ----------

    public function test_jeton_de_collecteur_sim_est_refuse_meme_avec_un_identifiant_identique(): void
    {
        $d = $this->makeUser('Dist', 'distributeur');
        $p = $this->makePlanning('P', ['distributors' => [$d]]);
        SimCollector::unguarded(fn () => SimCollector::create(['id' => $d->id, 'name' => 'Collecteur', 'status' => 'active']));
        $token = SimCollector::find($d->id)->createToken('mobile-app')->plainTextToken;

        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertStatus(403);
        $this->asToken($token)->getJson("/mobile/v1/distribution/plannings/{$p->id}/beneficiaries")->assertStatus(403);
    }

    public function test_compte_desactive_perd_immediatement_l_acces_malgre_un_jeton_valide(): void
    {
        $d = $this->makeUser('Dist', 'distributeur');
        $this->makePlanning('P', ['distributors' => [$d]]);
        $token = $this->postJson('/api/login', ['email' => $d->email, 'password' => 'Fictif-Passw0rd!'])->assertOk()->json('token');

        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertOk();
        $d->forceFill(['is_active' => false])->save();
        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertStatus(403);
    }

    public function test_jeton_trop_ancien_est_refuse_401_et_revoque(): void
    {
        $d = $this->makeUser('Dist', 'distributeur');
        $token = $this->postJson('/api/login', ['email' => $d->email, 'password' => 'Fictif-Passw0rd!'])->assertOk()->json('token');
        DB::table('personal_access_tokens')->update(['created_at' => now()->subDays(45)]);

        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertStatus(401);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_jeton_recent_reste_valide(): void
    {
        $d = $this->makeUser('Dist', 'distributeur');
        $token = $this->postJson('/api/login', ['email' => $d->email, 'password' => 'Fictif-Passw0rd!'])->assertOk()->json('token');
        DB::table('personal_access_tokens')->update(['created_at' => now()->subDays(5)]);

        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertOk();
    }

    public function test_deconnexion_serveur_revoque_le_jeton(): void
    {
        $d = $this->makeUser('Dist', 'distributeur');
        $token = $this->postJson('/api/login', ['email' => $d->email, 'password' => 'Fictif-Passw0rd!'])->assertOk()->json('token');

        $this->asToken($token)->postJson('/mobile/v1/distribution/logout')->assertOk();
        $this->assertSame(0, DB::table('personal_access_tokens')->count());

        $this->app['auth']->forgetGuards();
        $this->asToken($token)->getJson('/mobile/v1/distribution/my-plannings')->assertStatus(401);
    }
}
