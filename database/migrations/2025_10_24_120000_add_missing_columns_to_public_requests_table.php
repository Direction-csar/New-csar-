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
            // Ajouter le champ 'name' manquant
            if (!Schema::hasColumn('public_requests', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            
            // Ajouter le champ 'subject' manquant
            if (!Schema::hasColumn('public_requests', 'subject')) {
                $table->string('subject')->nullable()->after('email');
            }
            
            // Ajouter les champs de sécurité qui pourraient être utilisés
            if (!Schema::hasColumn('public_requests', 'duplicate_hash')) {
                $table->string('duplicate_hash')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('public_requests', 'urgency')) {
                $table->string('urgency')->default('medium')->after('description');
            }
            
            if (!Schema::hasColumn('public_requests', 'preferred_contact')) {
                $table->string('preferred_contact')->default('email')->after('urgency');
            }
            
            if (!Schema::hasColumn('public_requests', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('preferred_contact');
            }
            
            if (!Schema::hasColumn('public_requests', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $columns = ['name', 'subject', 'duplicate_hash', 'urgency', 'preferred_contact', 'ip_address', 'user_agent'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('public_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};









































