<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 40);
            $table->string('event_name', 120);
            $table->string('status', 30)->default('received');
            $table->string('queue_connection', 60)->nullable();
            $table->string('queue_name', 60)->nullable();
            $table->string('external_reference')->nullable();
            $table->string('summary')->nullable();
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['source', 'event_name']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_events');
    }
};
