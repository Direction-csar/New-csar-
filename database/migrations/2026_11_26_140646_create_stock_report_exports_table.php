<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_report_exports', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('portal', 20); // dg | dtl
            $table->string('report_type', 60);
            $table->json('filters')->nullable();
            $table->string('format', 10); // pdf | xlsx
            $table->string('filename');
            $table->string('path');
            $table->timestamps();

            $table->index(['portal', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_report_exports');
    }
};
