<?php

namespace Tests\Feature;

use App\Models\FuelEntry;
use App\Models\FuelExit;
use App\Models\FuelReimbursement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de l'API mobile « CSAR Comptable — module Carburant » :
 * authentification/rôle, entrées, sorties (autorisation/livraison),
 * remboursements (workflow complet) et effets sur le stock en litres.
 */
class FuelApiTest extends TestCase
{
    protected User $comptable;
    protected User $admin;
    protected User $agent;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->comptable = $this->makeUser('comptable');
        $this->admin = $this->makeUser('admin');
        $this->agent = $this->makeUser('magasinier');
        $this->warehouse = Warehouse::create([
            'name' => 'Cuve principale',
            'capacity' => 10000,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Fuel ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach ([
            'fuel_status_logs', 'fuel_attachments', 'fuel_reimbursements',
            'fuel_exits', 'fuel_entries', 'stock_movements', 'stocks',
            'stock_types', 'warehouses', 'personal_access_tokens', 'users',
        ] as $t) {
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

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('region')->nullable();
            $t->decimal('capacity', 15, 2)->nullable();
            $t->decimal('current_stock', 15, 2)->nullable();
            $t->string('status')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stock_types', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('display_name')->nullable();
            $t->string('unit')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->unsignedBigInteger('stock_type_id')->nullable();
            $t->string('item_name')->nullable();
            $t->decimal('quantity', 15, 2)->default(0);
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_id')->nullable();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('type');
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
            $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('quantity_before', 15, 2)->nullable();
            $t->decimal('quantity_after', 15, 2)->nullable();
            $t->decimal('prix_unitaire', 15, 2)->nullable();
            $t->decimal('total', 15, 2)->nullable();
            $t->string('reference')->nullable();
            $t->string('fournisseur')->nullable();
            $t->string('numero_facture')->nullable();
            $t->string('numero_bon')->nullable();
            $t->string('destinataire')->nullable();
            $t->date('date_mouvement')->nullable();
            $t->string('reason')->nullable();
            $t->string('motif')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->text('metadata')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('fuel_entries', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->date('operation_date');
            $t->unsignedBigInteger('warehouse_id');
            $t->string('supplier');
            $t->string('fuel_type', 40);
            $t->string('fuel_type_label')->nullable();
            $t->decimal('quantity', 12, 2);
            $t->decimal('unit_price', 12, 2);
            $t->decimal('total_amount', 14, 2);
            $t->string('invoice_number')->nullable();
            $t->date('invoice_date')->nullable();
            $t->string('delivery_note_ref')->nullable();
            $t->string('funding_source')->nullable();
            $t->text('notes')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('created_by');
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->unsignedBigInteger('stock_movement_id')->nullable();
            $t->string('idempotency_key')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('fuel_exits', function (Blueprint $t) {
            $t->id();
            $t->string('exit_number')->unique();
            $t->date('operation_date');
            $t->unsignedBigInteger('warehouse_id');
            $t->string('fuel_type', 40);
            $t->string('fuel_type_label')->nullable();
            $t->string('beneficiary_name');
            $t->unsignedBigInteger('beneficiary_id')->nullable();
            $t->string('vehicle_label')->nullable();
            $t->string('vehicle_plate')->nullable();
            $t->string('destination')->nullable();
            $t->string('purpose');
            $t->string('mission_number')->nullable();
            $t->date('mission_start_date')->nullable();
            $t->date('mission_end_date')->nullable();
            $t->string('authorization_ref')->nullable();
            $t->decimal('quantity_requested', 12, 2);
            $t->decimal('quantity_authorized', 12, 2)->nullable();
            $t->decimal('quantity_delivered', 12, 2)->nullable();
            $t->unsignedInteger('odometer_start')->nullable();
            $t->unsignedInteger('odometer_end')->nullable();
            $t->string('delivered_by_name')->nullable();
            $t->text('notes')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('created_by');
            $t->unsignedBigInteger('authorized_by')->nullable();
            $t->timestamp('authorized_at')->nullable();
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->unsignedBigInteger('stock_movement_id')->nullable();
            $t->string('idempotency_key')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('fuel_reimbursements', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->string('agent_name');
            $t->unsignedBigInteger('agent_id')->nullable();
            $t->string('service')->nullable();
            $t->date('expense_date');
            $t->string('purpose');
            $t->string('activity_ref')->nullable();
            $t->string('activity_type', 30)->nullable();
            $t->string('vehicle_label')->nullable();
            $t->string('vehicle_plate')->nullable();
            $t->decimal('amount_claimed', 14, 2);
            $t->decimal('quantity_liters', 12, 2)->nullable();
            $t->string('receipt_ref')->nullable();
            $t->decimal('amount_requested', 14, 2);
            $t->decimal('amount_approved', 14, 2)->nullable();
            $t->decimal('amount_paid', 14, 2)->nullable();
            $t->decimal('advance_amount', 14, 2)->nullable();
            $t->decimal('justified_amount', 14, 2)->nullable();
            $t->decimal('complement_amount', 14, 2)->nullable();
            $t->string('payment_ref')->nullable();
            $t->date('payment_date')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('created_by');
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->unsignedBigInteger('paid_by')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->text('review_comment')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->string('duplicate_of_ref')->nullable();
            $t->string('idempotency_key')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('fuel_attachments', function (Blueprint $t) {
            $t->id();
            $t->string('attachable_type');
            $t->unsignedBigInteger('attachable_id');
            $t->string('original_name');
            $t->string('stored_path');
            $t->string('mime_type', 100);
            $t->unsignedBigInteger('size');
            $t->string('sha256', 64);
            $t->string('kind', 40)->default('justificatif');
            $t->unsignedBigInteger('uploaded_by');
            $t->timestamps();
        });

        Schema::create('fuel_status_logs', function (Blueprint $t) {
            $t->id();
            $t->string('subject_type');
            $t->unsignedBigInteger('subject_id');
            $t->string('action', 60);
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->text('reason')->nullable();
            $t->unsignedBigInteger('actor_id');
            $t->timestamps();
        });
    }

    private function entryPayload(array $over = []): array
    {
        return array_merge([
            'operation_date' => '2026-10-28',
            'warehouse_id' => $this->warehouse->id,
            'supplier' => 'SENELEC Carburants',
            'fuel_type' => 'gasoil',
            'quantity' => 500,
            'unit_price' => 810,
        ], $over);
    }

    private function exitPayload(array $over = []): array
    {
        return array_merge([
            'operation_date' => '2026-10-29',
            'warehouse_id' => $this->warehouse->id,
            'fuel_type' => 'gasoil',
            'beneficiary_name' => 'Moussa Diop',
            'vehicle_label' => 'Toyota Hilux',
            'vehicle_plate' => 'DK-1234-AB',
            'purpose' => 'Mission terrain Kaolack',
            'quantity_requested' => 60,
        ], $over);
    }

    private function reimbPayload(array $over = []): array
    {
        return array_merge([
            'agent_name' => 'Awa Ndiaye',
            'service' => 'Logistique',
            'expense_date' => '2026-10-25',
            'purpose' => 'Achat carburant urgence mission',
            'amount_claimed' => 40500,
            'amount_requested' => 40500,
            'quantity_liters' => 50,
            'receipt_ref' => 'RECU-99',
        ], $over);
    }

    private function createEntry(): FuelEntry
    {
        $resp = $this->postJson('/api/comptable/v1/fuel/entries', $this->entryPayload());
        $resp->assertCreated();
        return FuelEntry::findOrFail($resp->json('entry.id'));
    }

    private function createExit(): FuelExit
    {
        $resp = $this->postJson('/api/comptable/v1/fuel/exits', $this->exitPayload());
        $resp->assertCreated();
        return FuelExit::findOrFail($resp->json('exit.id'));
    }

    private function createReimb(): FuelReimbursement
    {
        $resp = $this->postJson('/api/comptable/v1/fuel/reimbursements', $this->reimbPayload());
        $resp->assertCreated();
        return FuelReimbursement::findOrFail($resp->json('reimbursement.id'));
    }

    /* ===================== AUTH / RÔLES ===================== */

    /** @test Les endpoints carburant exigent un jeton Sanctum */
    public function test_fuel_endpoints_require_authentication(): void
    {
        $this->getJson('/api/comptable/v1/fuel/dashboard')->assertUnauthorized();
        $this->getJson('/api/comptable/v1/fuel/entries')->assertUnauthorized();
        $this->postJson('/api/comptable/v1/fuel/entries', [])->assertUnauthorized();
    }

    /** @test Un utilisateur sans rôle comptable/admin est refusé */
    public function test_fuel_denies_non_comptable_role(): void
    {
        Sanctum::actingAs($this->agent, ['comptable']);

        $this->getJson('/api/comptable/v1/fuel/dashboard')->assertForbidden();
        $this->postJson('/api/comptable/v1/fuel/entries', $this->entryPayload())
            ->assertForbidden();
    }

    /* ===================== ENTRÉES ===================== */

    /** @test Création d'entrée : brouillon + montant calculé */
    public function test_entry_creation_draft_with_total(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $entry = $this->createEntry();
        $this->assertSame('draft', $entry->status);
        $this->assertSame(405000.0, (float) $entry->total_amount); // 500 × 810
        $this->assertStringStartsWith('ENT-CARB-', $entry->reference);
    }

    /** @test La validation d'une entrée crédite le stock en litres */
    public function test_entry_validation_credits_stock(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $entry = $this->createEntry();

        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/validate")
            ->assertOk();

        $this->assertSame('validated', $entry->fresh()->status);
        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'type' => 'entry',
            'unite' => 'L',
            'status' => 'validated',
        ]);
    }

    /** @test Revalidation d'une entrée : succès idempotent, aucun mouvement en double */
    public function test_entry_validate_is_idempotent_guarded(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $entry = $this->createEntry();
        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/validate")->assertOk();

        // La seconde validation est un no-op (FuelService est idempotent).
        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/validate")
            ->assertOk();

        $this->assertSame(1, \App\Models\StockMovement::where('type', 'entry')
            ->where('unite', 'L')->count());
    }

    /** @test Le rejet d'une entrée exige un motif */
    public function test_entry_reject_requires_reason(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $entry = $this->createEntry();

        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/reject", [])
            ->assertStatus(422);
    }

    /* ===================== SORTIES ===================== */

    /** @test Autorisation refusée si le stock disponible est insuffisant */
    public function test_exit_authorize_blocked_without_stock(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $exit = $this->createExit();

        $this->postJson("/api/comptable/v1/fuel/exits/{$exit->id}/authorize", [
            'quantity_authorized' => 60,
        ])->assertStatus(422); // aucun litre en stock
    }

    /** @test Cycle complet : entrée validée → sortie autorisée → livrée débite le stock */
    public function test_exit_full_workflow_debits_stock(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $entry = $this->createEntry(); // 500 L gasoil
        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/validate")->assertOk();

        $exit = $this->createExit();
        $this->postJson("/api/comptable/v1/fuel/exits/{$exit->id}/authorize", [
            'quantity_authorized' => 60,
        ])->assertOk();

        $this->postJson("/api/comptable/v1/fuel/exits/{$exit->id}/validate", [
            'quantity_delivered' => 60,
        ])->assertOk();

        $this->assertSame('validated', $exit->fresh()->status);
        $this->assertSame(60.0, (float) $exit->fresh()->quantity_delivered);
    }

    /** @test On ne peut pas livrer plus que la quantité autorisée */
    public function test_exit_delivery_cannot_exceed_authorized(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $entry = $this->createEntry();
        $this->postJson("/api/comptable/v1/fuel/entries/{$entry->id}/validate")->assertOk();

        $exit = $this->createExit();
        $this->postJson("/api/comptable/v1/fuel/exits/{$exit->id}/authorize", [
            'quantity_authorized' => 40,
        ])->assertOk();

        $this->postJson("/api/comptable/v1/fuel/exits/{$exit->id}/validate", [
            'quantity_delivered' => 60,
        ])->assertStatus(422);
    }

    /* ===================== REMBOURSEMENTS ===================== */

    /** @test Demande créée en brouillon, soumission possible par le comptable */
    public function test_reimb_creation_and_submit(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $reimb = $this->createReimb();
        $this->assertSame('draft', $reimb->status);
        $this->assertStringStartsWith('REM-CARB-', $reimb->reference);

        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/submit")
            ->assertOk();
        $this->assertSame('submitted', $reimb->fresh()->status);
    }

    /** @test L'approbation financière est réservée au rôle admin */
    public function test_reimb_approve_requires_admin(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $reimb = $this->createReimb();
        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/submit")->assertOk();
        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/review")->assertOk();

        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/approve", [
            'amount_approved' => 40000,
        ])->assertForbidden(); // comptable ≠ admin
    }

    /** @test Workflow complet admin : instruction → approbation → paiement → clôture */
    public function test_reimb_full_workflow_with_admin(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $reimb = $this->createReimb();
        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/submit")->assertOk();
        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/review")->assertOk();
        $this->assertSame('in_review', $reimb->fresh()->status);

        Sanctum::actingAs($this->admin, ['comptable']);
        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/approve", [
            'amount_approved' => 40000,
        ])->assertOk();

        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/pay", [
            'payment_ref' => 'VIRT-2026-10-30-001',
            'payment_date' => '2026-10-30',
        ])->assertOk();
        $this->assertSame('paid', $reimb->fresh()->status);
        $this->assertSame(40000.0, (float) $reimb->fresh()->amount_paid);

        $this->postJson("/api/comptable/v1/fuel/reimbursements/{$reimb->id}/close")->assertOk();
        $this->assertSame('closed', $reimb->fresh()->status);
    }

    /** @test Le remboursement n'écrit jamais de mouvement de stock */
    public function test_reimb_does_not_touch_stock(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);
        $this->createReimb();

        $this->assertDatabaseMissing('stock_movements', [
            'metadata' => '%fuel_reimbursement%',
        ]);
    }

    /* ===================== DASHBOARD ===================== */

    /** @test Le dashboard renvoie soldes, valorisation et compteurs en attente */
    public function test_fuel_dashboard_payload(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->getJson('/api/comptable/v1/fuel/dashboard')
            ->assertOk()
            ->assertJsonStructure(['stock', 'stock_value', 'pending']);
    }
}
