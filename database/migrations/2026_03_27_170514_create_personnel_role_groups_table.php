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
        Schema::create('personnel_role_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('group_key');
            $table->unique(['role_id', 'group_key']);
            $table->index('group_key');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_role_groups');
    }
};
