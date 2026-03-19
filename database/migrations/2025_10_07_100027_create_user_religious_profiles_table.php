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
        Schema::create('user_religious_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->primary()
                ->constrained()
                ->onDelete('cascade');

            $table->date('baptism_date')->nullable();
            $table->string('baptism_place')->nullable();
            $table->string('baptismal_sponsor')->nullable();

            $table->date('first_communion_date')->nullable();
            $table->string('first_communion_place')->nullable();
            $table->string('first_communion_sponsor')->nullable();

            $table->date('confirmation_date')->nullable();
            $table->string('confirmation_place')->nullable();
            $table->string('confirmation_bishop')->nullable();

            $table->date('pledge_date')->nullable();
            $table->string('pledge_place')->nullable();
            $table->string('pledge_sponsor')->nullable();

            $table->enum('status_religious', ['in_course','graduated'])->default('in_course');
            $table->boolean('is_attendance')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_religious_profiles');
    }
};
