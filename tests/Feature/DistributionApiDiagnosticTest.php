<?php

namespace Tests\Feature;

use App\Models\DistributionBeneficiary;
use App\Models\DistributionEvent;
use App\Models\DistributionPlanning;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Diagnostic reproductible du flux Distribution mobile :
 * login agent, visibilité plannings/bénéficiaires, validation,
 * génération de ticket, collecte (clé location vs scan_location),
 * et schéma réel (registered_by absent).
 * Schéma minimal SQLite :memory: — aucune donnée réelle touchée.
 */
class DistributionApiDiagnosticTest extends TestCase
{
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->agent = User::create([
            'name' => 'Agent Distributeur',
            'email' => 'distributeur@csar.test',
            'password' => Hash::make('Distrib2026!'),
            'is_active' => true,
        ]);
        $this->agent->role = 'distributeur';
        $this->agent->role_id = \App\Models\Role::where('name', 'distributeur')->value('id'); // null : rôle absent de la table roles
        $this->agent->save();
    }

    private function createSchema(): void
    {
        foreach (['personal_access_tokens', 'distribution_scan_logs', 'distribution_tickets', 'distribution_beneficiaries', 'distribution_plannings', 'distribution_events', 'users', 'roles'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        // Même contenu qu'en base locale : NI 'distributeur' NI 'scanner'.
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'ctc'], ['id' => 2, 'name' => 'superviseur'],
            ['id' => 3, 'name' => 'drh'], ['id' => 4, 'name' => 'cpm'],
            ['id' => 5, 'name' => 'dpse'], ['id' => 6, 'name' => 'dtl'],
            ['id' => 7, 'name' => 'inspecteur'], ['id' => 8, 'name' => 'magasinier'],
            ['id' => 9, 'name' => 'comptable'], ['id' => 10, 'name' => 'admin'],
            ['id' => 11, 'name' => 'dg'], ['id' => 12, 'name' => 'responsable'],
            ['id' => 13, 'name' => 'agent'],
        ]);

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->unsignedInteger('role_id')->nullable();
            $t->string('phone')->nullable();
            $t->string('role')->default('agent');
            $t->boolean('is_active')->default(true);
            $t->boolean('two_factor_enabled')->default(false);
            $t->text('two_factor_secret')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_events', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('location')->nullable();
            $t->decimal('initial_stock_kg', 12, 2)->default(0);
            $t->datetime('start_date');
            $t->datetime('end_date')->nullable();
            $t->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $t->foreignId('created_by');
            $t->timestamps();
        });

        Schema::create('distribution_plannings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id');
            $t->string('name');
            $t->text('description')->nullable();
            $t->decimal('planned_quota_kg', 12, 2)->default(0);
            $t->decimal('executed_kg', 12, 2)->default(0);
            $t->integer('expected_beneficiaries')->default(0);
            $t->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $t->date('distribution_date')->nullable();
            $t->string('location')->nullable();
            $t->foreignId('assigned_to')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_beneficiaries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('planning_id');
            $t->string('full_name');
            $t->string('phone')->nullable();
            $t->string('cni')->nullable();
            $t->text('address')->nullable();
            $t->string('category')->nullable();
            $t->decimal('quantity_kg', 10, 2)->default(0);
            $t->boolean('is_vulnerable')->default(false);
            $t->boolean('is_pregnant')->default(false);
            $t->boolean('is_elderly')->default(false);
            $t->boolean('is_disabled')->default(false);
            $t->enum('status', ['pending', 'validated', 'ticket_issued', 'kit_collected'])->default('pending');
            $t->datetime('validated_at')->nullable();
            $t->foreignId('validated_by')->nullable();
            $t->foreignId('registered_by')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('beneficiary_id');
            $t->foreignId('planning_id');
            $t->string('ticket_code')->unique();
            $t->string('qr_token')->unique();
            $t->enum('status', ['issued', 'scanned', 'collected', 'cancelled'])->default('issued');
            $t->datetime('issued_at');
            $t->datetime('scanned_at')->nullable();
            $t->datetime('collected_at')->nullable();
            $t->foreignId('scanned_by')->nullable();
            $t->string('scan_location')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_scan_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id');
            $t->foreignId('user_id');
            $t->enum('action', ['scan', 'collect', 'cancel'])->default('scan');
            $t->text('notes')->nullable();
            $t->string('device_info')->nullable();
            $t->timestamps();
        });
    }

    private function makeEvent(array $attrs = []): DistributionEvent
    {
        return DistributionEvent::create(array_merge([
            'name' => 'Campagne Test',
            'slug' => 'campagne-' . substr(md5(uniqid()), 0, 8),
            'status' => 'active',
            'start_date' => now(),
            'created_by' => $this->agent->id,
        ], $attrs));
    }

    private function makePlanning(DistributionEvent $event, array $attrs = []): DistributionPlanning
    {
        return DistributionPlanning::create(array_merge([
            'event_id' => $event->id,
            'name' => 'Site ' . substr(md5(uniqid()), 0, 6),
            'planned_quota_kg' => 1000,
            'status' => 'active',
            'assigned_to' => $this->agent->id,
        ], $attrs));
    }

    // ===== E1 : compte agent =====

    public function test_distributeur_login_returns_token_despite_null_role_id(): void
    {
        // Le compte 'distributeur' a role_id NULL (rôle absent de la table roles),
        // ce qui ne bloque PAS le login : /api/login ne vérifie pas le rôle.
        $this->assertNull($this->agent->role_id);

        $this->postJson('/api/login', [
            'email' => 'distributeur@csar.test',
            'password' => 'Distrib2026!',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_inactive_account_rejected_403(): void
    {
        $inactive = User::create([
            'name' => 'Inactif', 'email' => 'inactif@csar.test',
            'password' => Hash::make('password123'), 'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'inactif@csar.test',
            'password' => 'password123',
        ])->assertForbidden();
    }

    // ===== E2 : plannings / bénéficiaires invisibles dans l'APK =====

    public function test_my_plannings_hides_draft_and_unassigned(): void
    {
        $event = $this->makeEvent();
        $assigned = $this->makePlanning($event, ['status' => 'active']);
        $draft = $this->makePlanning($event, ['status' => 'draft']);          // statut par défaut à la création web
        $unassigned = $this->makePlanning($event, ['assigned_to' => null]); // non attribué

        Sanctum::actingAs($this->agent);
        $ids = collect($this->getJson('/mobile/v1/distribution/my-plannings')->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($assigned->id));
        $this->assertFalse($ids->contains($draft->id), 'Un planning draft est invisible dans l’APK');
        $this->assertFalse($ids->contains($unassigned->id), 'Un planning non assigné est invisible dans l’APK');
    }

    public function test_planning_beneficiaries_returns_pending_with_null_ticket(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id,
            'full_name' => 'Test Beneficiaire',
            'quantity_kg' => 50,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->agent);
        $res = $this->getJson("/mobile/v1/distribution/plannings/{$planning->id}/beneficiaries");
        $res->assertOk()->assertJson(['success' => true]);

        $row = collect($res->json('data'))->firstWhere('id', $b->id);
        $this->assertNotNull($row);
        $this->assertSame('pending', $row['status']);
        $this->assertNull($row['ticket']); // ticket absent tant que generate-ticket n'est pas appelé
    }

    // ===== E3 / E4 : validation + ticket =====

    public function test_validate_writes_status_validator_and_date(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'pending',
        ]);

        Sanctum::actingAs($this->agent);
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/validate")->assertOk();

        $b->refresh();
        $this->assertSame('validated', $b->status);
        $this->assertNotNull($b->validated_at);
        $this->assertSame($this->agent->id, $b->validated_by);
    }

    public function test_generate_ticket_links_beneficiary_and_planning(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'validated',
            'validated_at' => now(), 'validated_by' => $this->agent->id,
        ]);

        Sanctum::actingAs($this->agent);
        $res = $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket");
        $res->assertOk();

        $ticket = DistributionTicket::first();
        $this->assertSame($b->id, $ticket->beneficiary_id);
        $this->assertSame($planning->id, $ticket->planning_id);
        $this->assertSame('ticket_issued', $b->fresh()->status);
    }

    public function test_generate_ticket_refused_on_pending(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'pending',
        ]);

        Sanctum::actingAs($this->agent);
        $this->postJson("/mobile/v1/distribution/beneficiaries/{$b->id}/generate-ticket")
            ->assertStatus(422);
        $this->assertSame(0, DistributionTicket::count());
    }

    // ===== E5 : collecte scanner — clé location vs scan_location =====

    public function test_collect_ignores_scan_location_key_sent_by_apk(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'ticket_issued',
        ]);
        $ticket = DistributionTicket::create([
            'beneficiary_id' => $b->id, 'planning_id' => $planning->id,
            'ticket_code' => 'CSAR-TEST01', 'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'issued', 'issued_at' => now(),
        ]);

        Sanctum::actingAs($this->agent);
        // scanner-app envoie 'scan_location' — le contrôleur accepte désormais les deux clés.
        $this->postJson("/mobile/v1/distribution/tickets/{$ticket->qr_token}/collect", [
            'scan_location' => 'Pikine',
        ])->assertOk();

        $this->assertSame('Pikine', $ticket->fresh()->scan_location);

        // Même appel avec la clé historique 'location' → également enregistré.
        $b2 = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B2', 'status' => 'ticket_issued',
        ]);
        $ticket2 = DistributionTicket::create([
            'beneficiary_id' => $b2->id, 'planning_id' => $planning->id,
            'ticket_code' => 'CSAR-TEST02', 'qr_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'issued', 'issued_at' => now(),
        ]);
        $this->postJson("/mobile/v1/distribution/tickets/{$ticket2->qr_token}/collect", [
            'location' => 'Pikine',
        ])->assertOk();
        $this->assertSame('Pikine', $ticket2->fresh()->scan_location);
    }

    // ===== Schéma : registered_by persiste =====

    public function test_registered_by_column_exists_and_api_writes_it(): void
    {
        $this->assertTrue(Schema::hasColumn('distribution_beneficiaries', 'registered_by'));

        $planning = $this->makePlanning($this->makeEvent());
        Sanctum::actingAs($this->agent);

        $this->postJson('/mobile/v1/distribution/beneficiaries', [
            'planning_id' => $planning->id,
            'full_name' => 'Beneficiaire Trace',
            'quantity_kg' => 25,
        ])->assertOk();

        $b = DistributionBeneficiary::where('full_name', 'Beneficiaire Trace')->firstOrFail();
        $this->assertSame($this->agent->id, $b->registered_by);
    }

    // ===== Actions web admin ajoutées (garde 'admin' + 2FA) =====

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
        ]);
        $admin->role = 'admin';
        $admin->save();
        return $admin;
    }

    public function test_web_validate_writes_status_validator_and_date(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'pending',
        ]);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post("/admin/distribution/beneficiaries/{$b->id}/validate")
            ->assertRedirect();

        $b->refresh();
        $this->assertSame('validated', $b->status);
        $this->assertNotNull($b->validated_at);
        $this->assertSame($admin->id, $b->validated_by);
    }

    public function test_web_edit_transition_to_validated_stamps_fields(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'pending',
            'quantity_kg' => 10,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put("/admin/distribution/beneficiaries/{$b->id}", [
                'planning_id' => $planning->id,
                'full_name' => 'B',
                'quantity_kg' => 10,
                'status' => 'validated',
            ])->assertRedirect();

        $b->refresh();
        $this->assertSame('validated', $b->status);
        $this->assertNotNull($b->validated_at);
        $this->assertSame($admin->id, $b->validated_by);
    }

    public function test_web_generate_ticket_links_beneficiary(): void
    {
        $planning = $this->makePlanning($this->makeEvent());
        $b = DistributionBeneficiary::create([
            'planning_id' => $planning->id, 'full_name' => 'B', 'status' => 'validated',
            'validated_at' => now(), 'validated_by' => $this->agent->id,
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->post("/admin/distribution/beneficiaries/{$b->id}/generate-ticket")
            ->assertRedirect();

        $ticket = DistributionTicket::firstOrFail();
        $this->assertSame($b->id, $ticket->beneficiary_id);
        $this->assertSame($planning->id, $ticket->planning_id);
        $this->assertSame('ticket_issued', $b->fresh()->status);
    }

    public function test_web_activate_makes_planning_visible_to_apk(): void
    {
        $planning = $this->makePlanning($this->makeEvent(), ['status' => 'draft']);

        Sanctum::actingAs($this->agent);
        $this->assertEmpty($this->getJson('/mobile/v1/distribution/my-plannings')->json('data'));

        $this->actingAs($this->admin(), 'admin')
            ->post("/admin/distribution/plannings/{$planning->id}/activate")
            ->assertRedirect();

        Sanctum::actingAs($this->agent);
        $ids = collect($this->getJson('/mobile/v1/distribution/my-plannings')->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($planning->id));
    }
}
