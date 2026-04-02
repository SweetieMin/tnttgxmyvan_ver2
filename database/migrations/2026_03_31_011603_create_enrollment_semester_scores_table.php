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
        Schema::create('enrollment_semester_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_enrollment_id')
                ->constrained('academic_enrollments')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->decimal('month_score_1', 5, 2)->nullable();
            $table->decimal('month_score_2', 5, 2)->nullable();
            $table->decimal('month_score_3', 5, 2)->nullable();
            $table->decimal('month_score_4', 5, 2)->nullable();
            $table->decimal('exam_score', 5, 2)->nullable();
            $table->decimal('catechism_score', 5, 2)->nullable();
            $table->decimal('conduct_score', 5, 2)->nullable();
            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['academic_enrollment_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_semester_scores');
    }
};
