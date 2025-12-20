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
        if (!Schema::hasTable('newsletters')) {
            Schema::create('newsletters', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subject');
                $table->longText('content');
                $table->string('template')->default('default');
                $table->enum('status', ['draft', 'pending', 'scheduled', 'sending', 'sent', 'failed'])->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->unsignedBigInteger('sent_by')->nullable();
                $table->integer('recipients_count')->default(0);
                $table->integer('delivered_count')->default(0);
                $table->integer('opened_count')->default(0);
                $table->integer('clicked_count')->default(0);
                $table->integer('bounced_count')->default(0);
                $table->integer('unsubscribed_count')->default(0);
                $table->decimal('open_rate', 5, 2)->default(0);
                $table->decimal('click_rate', 5, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['status', 'scheduled_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};
