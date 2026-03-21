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
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('ordering')->default(1000);
            $table->text('description');
            $table->enum('type', ['plus', 'minus']);
            $table->integer('points')->default(0);

            $table->enum('status', ['applied', 'not_applied', 'pending'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
