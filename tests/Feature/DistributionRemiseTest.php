<?php

namespace Tests\Feature;

use App\Models\DistributionBeneficiary;
use App\Models\DistributionScanAttempt;
use App\Models\DistributionScanLog;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

/**
 * Verification puis confirmation de la remise : usage unique du ticket,
 * droits de scan, journal d'audit. Donnees fictives, SQLite :memory:.
 */
class DistributionRemiseTest extends TestCase
{
    use BuildsDistributionSchema;

    private User $dist;
    private User $scanX;
    private User $scanY;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildDistributionSchema();
        $this->makeUser('Admin Test', 'admin');
        $this->dist = $this->makeUser('Distributeur', 'distributeur');
        $this->scanX = $this->makeUser('Scanner X', 'scanner');
        $this->scanY = $this->makeUser('Scanner Y', 'magasinier');
    }

    private function url(string $action, DistributionTicket $t): string
    {
        return "/mobile/v1/distribution/tickets/{$t->qr_token}/{$action}";
    }

    public function test_verification_ne_modifie_rien_et_renvoie_un_apercu_minimal(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX]]);
        $t = $this->makeTicket($p, 'T-1');
        $t->beneficiary->update(['phone' => '771234567', 'cni' => '1234567890123']);

        Sanctum::actingAs($this->scanX);
        $res = $this->postJson($this->url('verify', $t))->assertOk();

        $this->assertSame('Beneficiaire T-1', $res->json('data.beneficiary.full_name'));
        $this->assertSame('••••••567', $res->json('data.beneficiary.phone_masked'));
        $this->assertArrayNotHasKey('cni', $res->json('data.beneficiary'));
        $this->assertSame('issued', $t->fresh()->status);
        $this->assertSame('ticket_issued', $t->beneficiary->fresh()->status);
        $this->assertEquals(0.0, (float) $p->fresh()->executed_kg);
        $this->assertSame(0, DistributionScanLog::count());
    }

    public function test_remise_enregistre_agent_beneficiaire_planning_date_et_quantite(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX]]);
        $t = $this->makeTicket($p, 'T-2');

        Sanctum::actingAs($this->scanX);
        $res = $this->postJson($this->url('collect', $t), ['location' => 'Pikine', 'device_info' => 'Android test'])->assertOk();

        $t->refresh();
        $this->assertSame('collected', $t->status);
        $this->assertSame($this->scanX->id, (int) $t->scanned_by);
        $this->assertNotNull($t->collected_at);
        $this->assertSame('Pikine', $t->scan_location);
        $this->assertSame('kit_collected', $t->beneficiary->status);
        $this->assertEquals(50.0, (float) $p->fresh()->executed_kg);

        $log = DistributionScanLog::sole();
        $this->assertSame($t->id, (int) $log->ticket_id);
        $this->assertSame($this->scanX->id, (int) $log->user_id);

        $ok = DistributionScanAttempt::where('result', 'ok')->where('action', 'collect')->sole();
        $this->assertSame($this->scanX->id, (int) $ok->user_id);
        $this->assertSame($p->id, (int) $ok->planning_id);
        $this->assertSame($t->beneficiary_id, (int) $ok->beneficiary_id);

        // Compatibilite avec l'APK scanner deja installe.
        $this->assertSame('Beneficiaire T-2', $res->json('data.beneficiaire.full_name'));
        $this->assertSame('T-2', $res->json('data.ticket_code'));
    }

    public function test_ticket_deja_utilise_est_refuse_et_ne_compte_qu_une_fois(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX, $this->scanY]]);
        $t = $this->makeTicket($p, 'T-3');

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $t))->assertOk();
        Sanctum::actingAs($this->scanY);
        $this->postJson($this->url('collect', $t))->assertStatus(422)->assertJsonPath('message', 'Kit déjà récupéré.');
        $this->postJson($this->url('verify', $t))->assertStatus(422);

        $this->assertEquals(50.0, (float) $p->fresh()->executed_kg);
        $this->assertSame(1, DistributionScanLog::count());
        $this->assertSame($this->scanX->id, (int) $t->fresh()->scanned_by);
        $this->assertSame(2, DistributionScanAttempt::where('result', 'already_used')->count());
    }

    public function test_deux_scanners_en_course_un_seul_gagne_meme_si_les_deux_ont_vu_le_ticket_emis(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX, $this->scanY]]);
        $t = $this->makeTicket($p, 'T-4');
        $winner = $this->scanY;

        // Simule l'entrelacement : entre le controle (ticket encore « emis ») et l'UPDATE conditionnel,
        // l'autre scanner termine sa remise complete (ticket, beneficiaire, quantite).
        $fired = false;
        DB::beforeExecuting(function ($query, $bindings, $connection) use (&$fired, $t, $winner) {
            if ($fired || stripos($query, 'update "distribution_tickets" set "status"') !== 0) {
                return;
            }
            $fired = true;
            $pdo = $connection->getPdo();
            $pdo->exec("UPDATE distribution_tickets SET status='collected', collected_at=datetime('now'), scanned_by={$winner->id} WHERE id={$t->id}");
            $pdo->exec("UPDATE distribution_beneficiaries SET status='kit_collected' WHERE id={$t->beneficiary_id}");
            $pdo->exec("UPDATE distribution_plannings SET executed_kg = executed_kg + 50 WHERE id={$t->planning_id}");
        });

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $t))->assertStatus(422)->assertJsonPath('message', 'Kit déjà récupéré.');

        $this->assertTrue($fired, 'la course doit avoir ete simulee');
        $this->assertSame($winner->id, (int) $t->fresh()->scanned_by);
        $this->assertEquals(50.0, (float) $p->fresh()->executed_kg, 'les kilos ne sont comptes qu\'une fois');
        $this->assertSame(0, DistributionScanLog::count(), 'le perdant ne cree aucune remise');
        $this->assertSame(1, DistributionScanAttempt::where('user_id', $this->scanX->id)->where('result', 'already_used')->count());
    }

    public function test_beneficiaire_deja_servi_par_un_autre_ticket_est_refuse(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX]]);
        $t = $this->makeTicket($p, 'T-5');
        $t->beneficiary->update(['status' => 'kit_collected']);

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $t))->assertStatus(422);
        $this->assertSame('issued', $t->fresh()->status);
        $this->assertEquals(0.0, (float) $p->fresh()->executed_kg);
    }

    public function test_scanner_non_affecte_au_planning_est_refuse_et_la_tentative_est_journalisee(): void
    {
        $p = $this->makePlanning('P', ['scanners' => [$this->scanY]]);
        $t = $this->makeTicket($p, 'T-6');

        Sanctum::actingAs($this->scanX); // scanner sur un AUTRE planning seulement
        $this->postJson($this->url('collect', $t))->assertStatus(403);
        $this->postJson($this->url('verify', $t))->assertStatus(403);

        $this->assertSame('issued', $t->fresh()->status);
        $this->assertSame(0, DistributionScanLog::count());
        $a = DistributionScanAttempt::where('user_id', $this->scanX->id)->where('result', 'forbidden')->get();
        $this->assertCount(2, $a);
        $this->assertSame($t->id, (int) $a->first()->ticket_id);
    }

    public function test_un_scanner_n_a_acces_qu_aux_tickets_de_ses_plannings(): void
    {
        $mine = $this->makePlanning('Mien', ['scanners' => [$this->scanX]]);
        $other = $this->makePlanning('Autre', ['scanners' => [$this->scanY]]);
        $tMine = $this->makeTicket($mine, 'T-7');
        $tOther = $this->makeTicket($other, 'T-8');

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $tMine))->assertOk();
        $this->postJson($this->url('collect', $tOther))->assertStatus(403);
        $this->getJson("/mobile/v1/distribution/scan/plannings/{$other->id}/tickets")->assertStatus(404);
    }

    public function test_code_inconnu_404_journalise_avec_l_agent(): void
    {
        $this->makePlanning('P', ['scanners' => [$this->scanX]]);

        Sanctum::actingAs($this->scanX);
        $this->postJson('/mobile/v1/distribution/tickets/INCONNU/collect')->assertStatus(404);

        $a = DistributionScanAttempt::sole();
        $this->assertSame('invalid_code', $a->result);
        $this->assertSame($this->scanX->id, (int) $a->user_id);
        $this->assertNull($a->ticket_id);
    }

    public function test_distributeur_ne_confirme_pas_la_remise_sauf_s_il_est_aussi_scanner_sur_ce_planning(): void
    {
        $p = $this->makePlanning('P', ['distributors' => [$this->dist]]);
        $t = $this->makeTicket($p, 'T-9');

        Sanctum::actingAs($this->dist);
        $this->postJson($this->url('collect', $t))->assertStatus(403);

        $p->syncAgents('scanner', [$this->dist->id]);
        $this->postJson($this->url('collect', $t))->assertOk();

        // Une affectation scanner sur un autre planning ne suffit pas.
        $p2 = $this->makePlanning('P2', ['distributors' => [$this->dist]]);
        $t2 = $this->makeTicket($p2, 'T-10');
        $this->postJson($this->url('collect', $t2))->assertStatus(403);
    }

    public function test_planning_inactif_ou_ticket_annule_sont_refuses(): void
    {
        $done = $this->makePlanning('Termine', ['scanners' => [$this->scanX], 'status' => 'completed']);
        $tDone = $this->makeTicket($done, 'T-11');
        $p = $this->makePlanning('P', ['scanners' => [$this->scanX]]);
        $tCancelled = $this->makeTicket($p, 'T-12', 'cancelled');

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $tDone))->assertStatus(422);
        $this->postJson($this->url('collect', $tCancelled))->assertStatus(422)->assertJsonPath('message', 'Ticket annulé.');
        $this->assertSame(1, DistributionScanAttempt::where('result', 'planning_inactive')->count());
        $this->assertSame(1, DistributionScanAttempt::where('result', 'cancelled')->count());
    }

    public function test_ticket_incoherent_beneficiaire_d_un_autre_planning_est_refuse(): void
    {
        $p1 = $this->makePlanning('P1', ['scanners' => [$this->scanX]]);
        $p2 = $this->makePlanning('P2', ['scanners' => [$this->scanX]]);
        $t = $this->makeTicket($p1, 'T-13');
        DistributionBeneficiary::whereKey($t->beneficiary_id)->update(['planning_id' => $p2->id]);

        Sanctum::actingAs($this->scanX);
        $this->postJson($this->url('collect', $t))->assertStatus(409);
        $this->assertSame('issued', $t->fresh()->status);
    }

    public function test_emission_du_ticket_enregistre_l_agent_distributeur_et_reste_distincte_de_la_remise(): void
    {
        $p = $this->makePlanning('P', ['distributors' => [$this->dist]]);
        $b = $this->makeBeneficiary($p, 'B', 'validated');

        Sanctum::actingAs($this->dist);
        $res = $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket")->assertOk();

        $ticket = DistributionTicket::sole();
        $this->assertSame($this->dist->id, (int) $ticket->issued_by);
        $this->assertSame('issued', $ticket->status);
        $this->assertSame('ticket_issued', $b->fresh()->status);
        $this->assertEquals(0.0, (float) $p->fresh()->executed_kg);
        $this->assertNotEmpty($res->json('data.qr_token'));

        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket")->assertStatus(422);
        $this->assertSame(1, DistributionTicket::count());
    }
}
