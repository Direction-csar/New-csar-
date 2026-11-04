<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $table->string('structure_name')->nullable()->after('ninea');
            $table->date('event_date')->nullable()->after('structure_name');
            $table->string('event_location')->nullable()->after('event_date');
            $table->unsignedInteger('event_participants')->nullable()->after('event_location');
        });
    }

    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $table->dropColumn([
                'structure_name',
                'event_date', 'event_location', 'event_participants',
            ]);
        });
    }
};
