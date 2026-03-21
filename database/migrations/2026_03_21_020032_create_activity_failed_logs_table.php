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
        Schema::create('activity_failed_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->index();
            $table->string('action')->index();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->text('message');
            $table->string('exception')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_failed_logs');
    }
};
