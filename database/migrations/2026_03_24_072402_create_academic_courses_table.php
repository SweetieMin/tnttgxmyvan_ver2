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
        Schema::create('academic_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->string('course_name');
            $table->string('sector_name');

            // 🔹 Cài đặt điểm chuẩn cần đạt được
            $table->decimal('catechism_avg_score', 5, 2)->default(5.00);
            $table->decimal('catechism_training_score', 5, 2)->default(5.00);

            $table->unsignedSmallInteger('activity_score')->default(150);

            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['academic_year_id', 'course_name', 'sector_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_courses');
    }
};
