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
        Schema::create('enrollment_activity_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_enrollment_id')
                ->constrained('academic_enrollments')
                ->cascadeOnDelete();
            $table->foreignId('attendance_checkin_id')
                ->nullable()
                ->constrained('attendance_checkins')
                ->nullOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->integer('points');
            $table->timestamp('happened_at');
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_activity_points');
    }
};
