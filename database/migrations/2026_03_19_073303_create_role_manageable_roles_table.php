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
        Schema::create('role_manageable_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_role_id')
                ->constrained('roles')
                ->cascadeOnDelete();
            $table->foreignId('manageable_role_id')
                ->constrained('roles')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['manager_role_id', 'manageable_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_manageable_roles');
    }
};
