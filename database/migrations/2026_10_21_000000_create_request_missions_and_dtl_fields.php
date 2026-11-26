<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Champs de transmission au DTL sur les demandes publiques
        // Garde hasTable : la table peut être absente sur une base partielle.
        if (Schema::hasTable('public_requests')) {
            Schema::table('public_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('public_requests', 'dtl_transmitted_at')) {
                    $table->timestamp('dtl_transmitted_at')->nullable()->after('dg_approved_at');
                }
                if (!Schema::hasColumn('public_requests', 'dtl_transmitted_by')) {
                    $table->unsignedBigInteger('dtl_transmitted_by')->nullable()->after('dtl_transmitted_at');
                }
                if (!Schema::hasColumn('public_requests', 'dtl_instructions')) {
                    $table->text('dtl_instructions')->nullable()->after('dtl_transmitted_by');
                }
            });
        }

        // Missions opérationnelles DTL : inspections terrain et sorties de stock
        // Nécessite public_requests (FK) : sans elle, la création échouerait.
        if (!Schema::hasTable('request_missions') && Schema::hasTable('public_requests')) {
            Schema::create('request_missions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('public_request_id')->constrained('public_requests')->onDelete('cascade');
                $table->string('type'); // inspection | stock_exit
                $table->unsignedBigInteger('assigned_by')->nullable();   // utilisateur DTL
                $table->unsignedBigInteger('assigned_to')->nullable();   // inspecteur ou magasinier
                $table->text('instructions')->nullable();
                $table->string('status')->default('affectee'); // affectee, en_cours, rapport_soumis, terminee, annulee
                $table->text('report')->nullable();
                $table->json('attachments')->nullable();
                $table->decimal('visit_latitude', 10, 8)->nullable();
                $table->decimal('visit_longitude', 11, 8)->nullable();
                $table->timestamp('visited_at')->nullable();
                $table->timestamp('reported_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                // Revue du rapport par le DTL : validation ou demande de correction
                $table->text('review_comment')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['public_request_id', 'type']);
                $table->index(['assigned_to', 'status']);
            });
        }

        // Lien optionnel des sorties de stock vers une mission/demande
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'public_request_id')) {
                    $table->unsignedBigInteger('public_request_id')->nullable();
                }
                if (!Schema::hasColumn('stock_movements', 'request_mission_id')) {
                    $table->unsignedBigInteger('request_mission_id')->nullable();
                }
            });
        }

        // Rôle inspecteur si absent
        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'inspecteur')->exists()) {
            DB::table('roles')->insert([
                'name' => 'inspecteur',
                'display_name' => 'Inspecteur terrain',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'magasinier')->exists()) {
            DB::table('roles')->insert([
                'name' => 'magasinier',
                'display_name' => 'Magasinier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                foreach (['public_request_id', 'request_mission_id'] as $col) {
                    if (Schema::hasColumn('stock_movements', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        Schema::dropIfExists('request_missions');
        if (Schema::hasTable('public_requests')) {
            Schema::table('public_requests', function (Blueprint $table) {
                foreach (['dtl_transmitted_at', 'dtl_transmitted_by', 'dtl_instructions'] as $col) {
                    if (Schema::hasColumn('public_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
