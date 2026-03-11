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
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('email');
            $table->string('status')->default('active')->after('is_active');
            $table->timestamp('unsubscribed_at')->nullable()->after('subscribed_at');
            $table->string('source')->default('website')->after('unsubscribed_at');
            $table->json('preferences')->nullable()->after('source');
            $table->timestamp('last_email_sent_at')->nullable()->after('preferences');
            $table->integer('email_count')->default(0)->after('last_email_sent_at');
        });

        // SQLite/MySQL: supprimer l'index avant de supprimer la colonne
        if (Schema::hasColumn('newsletter_subscribers', 'is_active')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $driver = Schema::getConnection()->getDriverName();
                if ($driver === 'sqlite') {
                    $table->dropIndex('newsletter_subscribers_is_active_index');
                } else {
                    $table->dropIndex(['is_active']);
                }
            });
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'status', 
                'unsubscribed_at',
                'source',
                'preferences',
                'last_email_sent_at',
                'email_count'
            ]);
            
            // Restaurer l'ancienne colonne is_active
            $table->boolean('is_active')->default(true);
        });
    }
};
