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
        Schema::create('user_parents', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->primary()
                ->constrained()
                ->onDelete('cascade');
            $table->string('christian_name_father')->nullable();
            $table->string('name_father')->nullable();
            $table->string('phone_father')->nullable();

            $table->string('christian_name_mother')->nullable();
            $table->string('name_mother')->nullable();
            $table->string('phone_mother')->nullable();

            $table->string('christian_name_god_parent')->nullable();
            $table->string('name_god_parent')->nullable();
            $table->string('phone_god_parent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_parents');
    }
};
