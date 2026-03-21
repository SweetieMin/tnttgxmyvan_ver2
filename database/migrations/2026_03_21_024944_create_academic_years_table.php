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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // 🔹 Thời gian cho chương trình Giáo lý
            $table->date('catechism_start_date')->nullable();
            $table->date('catechism_end_date')->nullable();

            // 🔹 Cài đặt điểm chuẩn cần đạt được
            $table->decimal('catechism_avg_score', 5, 2)->default(5.00);
            $table->decimal('catechism_training_score', 5, 2)->default(5.00);

            // 🔹 Thời gian cho Sinh hoạt - điểm danh TNTT
            $table->date('activity_start_date')->nullable();
            $table->date('activity_end_date')->nullable();

            $table->unsignedSmallInteger('activity_score')->default(150);

            $table->enum('status_academic', ['upcoming', 'ongoing', 'finished'])->default('upcoming');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
