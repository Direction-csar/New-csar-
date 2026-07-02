<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            // Workflow avancé
            if (!Schema::hasColumn('public_requests', 'workflow_status')) {
                $table->enum('workflow_status', [
                    'soumise',
                    'en_revue',
                    'document_attente',
                    'signee',
                    'scannee',
                    'validee_dg',
                    'approuvee',
                    'rejetee',
                    'cloturee'
                ])->default('soumise')->after('status');
            }

            // Documents de courrier
            if (!Schema::hasColumn('public_requests', 'courier_reference')) {
                $table->string('courier_reference')->nullable()->after('workflow_status');
            }
            if (!Schema::hasColumn('public_requests', 'courier_date')) {
                $table->date('courier_date')->nullable()->after('courier_reference');
            }
            if (!Schema::hasColumn('public_requests', 'dg_signature_file')) {
                $table->string('dg_signature_file')->nullable()->after('courier_date');
            }
            if (!Schema::hasColumn('public_requests', 'scan_file')) {
                $table->string('scan_file')->nullable()->after('dg_signature_file');
            }
            if (!Schema::hasColumn('public_requests', 'document_notes')) {
                $table->text('document_notes')->nullable()->after('scan_file');
            }

            // Traçabilité
            if (!Schema::hasColumn('public_requests', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->after('assigned_to');
            }
            if (!Schema::hasColumn('public_requests', 'dg_approved_by')) {
                $table->foreignId('dg_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('processed_by');
            }
            if (!Schema::hasColumn('public_requests', 'dg_approved_at')) {
                $table->timestamp('dg_approved_at')->nullable()->after('dg_approved_by');
            }

            // Identification demandeur
            if (!Schema::hasColumn('public_requests', 'requester_id')) {
                $table->string('requester_id', 64)->nullable()->index()->after('duplicate_hash');
            }
            if (!Schema::hasColumn('public_requests', 'duplicate_of')) {
                $table->foreignId('duplicate_of')->nullable()->constrained('public_requests')->nullOnDelete()->after('requester_id');
            }
            if (!Schema::hasColumn('public_requests', 'is_duplicate')) {
                $table->boolean('is_duplicate')->default(false)->after('duplicate_of');
            }

            // Historique d'actions
            if (!Schema::hasColumn('public_requests', 'workflow_history')) {
                $table->json('workflow_history')->nullable()->after('dg_approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $columns = [
                'workflow_status',
                'courier_reference',
                'courier_date',
                'dg_signature_file',
                'scan_file',
                'document_notes',
                'processed_by',
                'dg_approved_by',
                'dg_approved_at',
                'requester_id',
                'duplicate_of',
                'is_duplicate',
                'workflow_history',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('public_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
