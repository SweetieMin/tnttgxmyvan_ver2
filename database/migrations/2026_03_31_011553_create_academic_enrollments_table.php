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
        Schema::create('academic_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();
            $table->foreignId('academic_course_id')
                ->constrained('academic_courses')
                ->cascadeOnDelete();
            $table->string('status')->default('studying');
            $table->decimal('final_catechism_score', 5, 2)->nullable();
            $table->decimal('final_conduct_score', 5, 2)->nullable();
            $table->integer('final_activity_score')->nullable();
            $table->boolean('is_eligible_for_promotion')->nullable();
            $table->string('review_status')->default('not_required');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_enrollments');
    }
};
