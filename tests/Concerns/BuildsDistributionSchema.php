<?php

namespace Tests\Concerns;

use App\Models\DistributionBeneficiary;
use App\Models\DistributionEvent;
use App\Models\DistributionPlanning;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Schema minimal SQLite :memory: du module Distribution (affectations multiples
 * incluses) et fabriques de donnees FICTIVES. Aucune donnee reelle n'est touchee.
 */
trait BuildsDistributionSchema
{
    protected ?DistributionEvent $sharedEvent = null;

    protected function buildDistributionSchema(): void
    {
        $this->sharedEvent = null;

        foreach ([
            'distribution_scan_attempts', 'distribution_scan_logs', 'public_requests', 'distribution_tickets',
            'distribution_beneficiaries', 'distribution_planning_agents', 'distribution_plannings',
            'distribution_events', 'personal_access_tokens', 'sim_collectors', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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
            $t->timestamp('last_activity')->nullable();
            $t->timestamps();
        });

        Schema::create('sim_collectors', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('password_hash')->nullable();
            $t->json('assigned_zones')->nullable();
            $t->string('status')->default('active');
            $t->timestamp('last_sync')->nullable();
            $t->integer('total_collections')->default(0);
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
            $t->string('status')->default('draft');
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
            $t->string('status')->default('draft');
            $t->date('distribution_date')->nullable();
            $t->string('location')->nullable();
            $t->foreignId('assigned_to')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_planning_agents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('planning_id');
            $t->foreignId('user_id');
            $t->string('type', 20);
            $t->foreignId('assigned_by')->nullable();
            $t->timestamps();
            $t->unique(['planning_id', 'user_id', 'type']);
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
            foreach (['is_vulnerable', 'is_pregnant', 'is_elderly', 'is_disabled'] as $c) {
                $t->boolean($c)->default(false);
            }
            $t->string('status')->default('pending');
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
            $t->string('status')->default('issued');
            $t->datetime('issued_at');
            $t->datetime('scanned_at')->nullable();
            $t->datetime('collected_at')->nullable();
            $t->foreignId('scanned_by')->nullable();
            $t->foreignId('issued_by')->nullable();
            $t->string('scan_location')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_scan_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ticket_id');
            $t->foreignId('user_id');
            $t->string('action')->default('scan');
            $t->text('notes')->nullable();
            $t->string('device_info')->nullable();
            $t->timestamps();
        });

        Schema::create('distribution_scan_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable();
            $t->foreignId('ticket_id')->nullable();
            $t->foreignId('planning_id')->nullable();
            $t->foreignId('beneficiary_id')->nullable();
            $t->string('action', 20);
            $t->string('result', 40);
            $t->string('code', 100)->nullable();
            $t->string('message')->nullable();
            $t->string('device_info')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });

        // Uniquement pour le badge du layout admin (comptage par statut de workflow).
        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('workflow_status')->default('soumise');
            $t->timestamps();
        });
    }

    protected function makeUser(string $name, string $role, bool $active = true): User
    {
        $user = User::create([
            'name' => $name,
            'email' => strtolower(preg_replace('/[^a-z0-9]+/i', '.', $name)) . '@csar.test',
            'password' => Hash::make('Fictif-Passw0rd!'),
            'is_active' => $active,
        ]);
        $user->role = $role;
        $user->save();

        return $user;
    }

    protected function makeEvent(string $status = 'active'): DistributionEvent
    {
        return DistributionEvent::create([
            'name' => 'Campagne fictive',
            'slug' => 'campagne-' . substr(md5(uniqid('', true)), 0, 8),
            'status' => $status,
            'start_date' => now(),
            'created_by' => User::query()->value('id') ?? 1,
        ]);
    }

    /** @param array{distributors?: User[], scanners?: User[], status?: string} $opts */
    protected function makePlanning(string $name, array $opts = []): DistributionPlanning
    {
        $planning = DistributionPlanning::create([
            'event_id' => ($this->sharedEvent ??= $this->makeEvent())->id,
            'name' => $name,
            'planned_quota_kg' => 1000,
            'status' => $opts['status'] ?? 'active',
        ]);

        $planning->syncAgents('distributor', collect($opts['distributors'] ?? [])->pluck('id')->all());
        $planning->syncAgents('scanner', collect($opts['scanners'] ?? [])->pluck('id')->all());

        return $planning->fresh();
    }

    protected function makeBeneficiary(DistributionPlanning $planning, string $name, string $status = 'pending', float $kg = 50): DistributionBeneficiary
    {
        return DistributionBeneficiary::create([
            'planning_id' => $planning->id,
            'full_name' => $name,
            'quantity_kg' => $kg,
            'status' => $status,
        ]);
    }

    protected function makeTicket(DistributionPlanning $planning, string $code, string $ticketStatus = 'issued'): DistributionTicket
    {
        $beneficiary = $this->makeBeneficiary(
            $planning,
            'Beneficiaire ' . $code,
            $ticketStatus === 'collected' ? 'kit_collected' : 'ticket_issued'
        );

        return DistributionTicket::create([
            'beneficiary_id' => $beneficiary->id,
            'planning_id' => $planning->id,
            'ticket_code' => $code,
            'qr_token' => 'qr-' . $code,
            'status' => $ticketStatus,
            'issued_at' => now(),
        ]);
    }
}
