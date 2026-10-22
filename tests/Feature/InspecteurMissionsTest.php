<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\RequestMission;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tests du circuit DTL → Inspecteur : affectation, prise en charge,
 * rapport de visite, validation ou demande de correction par le DTL.
 * Schéma minimal SQLite :memory: comme ComptableMouvementsTest.
 */
class InspecteurMissionsTest extends TestCase
{
    protected User $dtl;
    protected User $inspecteur;
    protected User $autreInspecteur;
    protected User $magasinier;
    protected PublicRequest $demande;
    protected RequestMission $mission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->dtl = $this->makeUser('dtl');
        $this->inspecteur = $this->makeUser('inspecteur');
        $this->autreInspecteur = $this->makeUser('inspecteur');
        $this->magasinier = $this->makeUser('magasinier');

        $this->demande = PublicRequest::create([
            'tracking_code' => 'CSAR-TST' . strtoupper(substr(md5(uniqid()), 0, 6)),
            'type' => 'aide_alimentaire',
            'status' => 'approved',
            'workflow_status' => 'approuvee',
            'full_name' => 'Demandeur Inspection',
            'phone' => '770000001',
            'region' => 'Dakar',
            'dtl_transmitted_at' => now(),
        ]);

        $this->mission = RequestMission::create([
            'public_request_id' => $this->demande->id,
            'type' => RequestMission::TYPE_INSPECTION,
            'assigned_by' => $this->dtl->id,
            'assigned_to' => $this->inspecteur->id,
            'instructions' => 'Vérifier le site du bénéficiaire.',
            'status' => 'affectee',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach (['request_missions', 'public_requests', 'notifications', 'users'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->default('agent');
            $t->unsignedBigInteger('role_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('type')->nullable();
            $t->string('title')->nullable();
            $t->text('message')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('tracking_code')->unique();
            $t->string('type')->nullable();
            $t->string('status')->default('pending');
            $t->string('workflow_status')->default('soumise');
            $t->string('full_name')->nullable();
            $t->string('phone')->nullable();
            $t->string('region')->nullable();
            $t->text('description')->nullable();
            $t->text('dtl_instructions')->nullable();
            $t->timestamp('dtl_transmitted_at')->nullable();
            $t->unsignedBigInteger('dtl_transmitted_by')->nullable();
            $t->text('workflow_history')->nullable();
            $t->timestamps();
        });

        Schema::create('request_missions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('public_request_id');
            $t->string('type');
            $t->unsignedBigInteger('assigned_by')->nullable();
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->text('instructions')->nullable();
            $t->string('status')->default('affectee');
            $t->text('report')->nullable();
            $t->json('attachments')->nullable();
            $t->decimal('visit_latitude', 10, 8)->nullable();
            $t->decimal('visit_longitude', 11, 8)->nullable();
            $t->timestamp('visited_at')->nullable();
            $t->timestamp('reported_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->text('review_comment')->nullable();
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->timestamps();
        });
    }

    /** @test Le DTL affecte une mission inspection à un inspecteur */
    public function test_dtl_assigns_inspection_mission(): void
    {
        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/demandes/{$this->demande->id}/missions", [
                'type' => 'inspection',
                'assigned_to' => $this->inspecteur->id,
                'instructions' => 'Inspection du site demandeur.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('request_missions', [
            'public_request_id' => $this->demande->id,
            'type' => 'inspection',
            'assigned_to' => $this->inspecteur->id,
            'status' => 'affectee',
            'instructions' => 'Inspection du site demandeur.',
        ]);
        // 2 missions au total : celle du setUp + la nouvelle
        $this->assertSame(2, RequestMission::where('public_request_id', $this->demande->id)->count());
    }

    /** @test Le DTL ne peut pas affecter une mission inspection à un magasinier */
    public function test_dtl_cannot_assign_inspection_to_wrong_role(): void
    {
        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/demandes/{$this->demande->id}/missions", [
                'type' => 'inspection',
                'assigned_to' => $this->magasinier->id,
                'instructions' => 'Inspection du site.',
            ]);

        $this->assertDatabaseMissing('request_missions', [
            'type' => 'inspection',
            'assigned_to' => $this->magasinier->id,
        ]);
    }

    /** @test Un inspecteur ne peut pas accéder aux routes DTL */
    public function test_inspector_cannot_access_dtl_routes(): void
    {
        $count = RequestMission::count();

        $this->actingAs($this->inspecteur, 'inspecteur')
            ->post("/dtl/demandes/{$this->demande->id}/missions", [
                'type' => 'inspection',
                'assigned_to' => $this->inspecteur->id,
                'instructions' => 'Tentative non autorisée.',
            ])
            ->assertRedirect(route('dtl.login'));

        $this->assertSame($count, RequestMission::count());
    }

    /** @test L'inspecteur ne voit pas la mission d'un collègue */
    public function test_inspector_cannot_view_others_mission(): void
    {
        $this->actingAs($this->autreInspecteur, 'inspecteur')
            ->get("/inspecteur/missions/{$this->mission->id}")
            ->assertNotFound();
    }

    /** @test L'inspecteur consulte sa mission et la prend en charge */
    public function test_inspector_views_and_starts_mission(): void
    {
        $this->actingAs($this->inspecteur, 'inspecteur')
            ->get("/inspecteur/missions/{$this->mission->id}")
            ->assertOk()
            ->assertSee($this->demande->tracking_code);

        $this->actingAs($this->inspecteur, 'inspecteur')
            ->post("/inspecteur/missions/{$this->mission->id}/start")
            ->assertRedirect();

        $this->assertSame('en_cours', $this->mission->fresh()->status);
    }

    /** @test L'inspecteur soumet un rapport → statut rapport_soumis */
    public function test_inspector_submits_report(): void
    {
        $this->mission->update(['status' => 'en_cours']);

        $this->actingAs($this->inspecteur, 'inspecteur')
            ->post("/inspecteur/missions/{$this->mission->id}/report", [
                'report' => 'Visite effectuée : le bénéficiaire est bien présent et éligible.',
                'latitude' => 14.69,
                'longitude' => -17.44,
            ])
            ->assertRedirect(route('inspecteur.missions.index'));

        $m = $this->mission->fresh();
        $this->assertSame('rapport_soumis', $m->status);
        $this->assertNotNull($m->reported_at);
        $this->assertNotNull($m->visited_at);
    }

    /** @test Un rapport trop court est refusé */
    public function test_report_requires_minimum_length(): void
    {
        $this->mission->update(['status' => 'en_cours']);

        $this->actingAs($this->inspecteur, 'inspecteur')
            ->post("/inspecteur/missions/{$this->mission->id}/report", [
                'report' => 'Trop court.',
            ])
            ->assertSessionHasErrors('report');
    }

    /** @test Le DTL valide un rapport soumis → mission terminée */
    public function test_dtl_validates_submitted_report(): void
    {
        $this->mission->update(['status' => 'rapport_soumis', 'report' => 'Rapport complet de la visite.']);

        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/missions/{$this->mission->id}/validate")
            ->assertRedirect();

        $m = $this->mission->fresh();
        $this->assertSame('terminee', $m->status);
        $this->assertNotNull($m->completed_at);
        $this->assertSame($this->dtl->id, $m->reviewed_by);
    }

    /** @test Le DTL demande une correction → mission repasse en cours avec consigne */
    public function test_dtl_requests_correction(): void
    {
        $this->mission->update(['status' => 'rapport_soumis', 'report' => 'Rapport incomplet.']);

        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/missions/{$this->mission->id}/correct", [
                'review_comment' => 'Ajouter les photos du stock observé.',
            ])
            ->assertRedirect();

        $m = $this->mission->fresh();
        $this->assertSame('en_cours', $m->status);
        $this->assertSame('Ajouter les photos du stock observé.', $m->review_comment);
    }

    /** @test Une mission déjà terminée ne peut plus être validée ni corrigée */
    public function test_completed_mission_cannot_be_reviewed(): void
    {
        $this->mission->update(['status' => 'terminee', 'completed_at' => now()]);

        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/missions/{$this->mission->id}/validate");

        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/missions/{$this->mission->id}/correct", [
                'review_comment' => 'Correction tardive.',
            ]);

        $this->assertSame('terminee', $this->mission->fresh()->status);
    }

    /** @test Le DTL annule une mission active avec motif */
    public function test_dtl_cancels_mission_with_reason(): void
    {
        $this->actingAs($this->dtl, 'dtl')
            ->post("/dtl/missions/{$this->mission->id}/cancel", [
                'reason' => 'Site inaccessible.',
            ])
            ->assertRedirect();

        $this->assertSame('annulee', $this->mission->fresh()->status);
    }
}
