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
        Schema::create('attendance_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_schedule_id')
                ->constrained('attendance_schedules')
                ->cascadeOnDelete();
            $table->foreignId('academic_enrollment_id')
                ->constrained('academic_enrollments')
                ->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->string('checkin_method')->default('qr');
            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(
                ['attendance_schedule_id', 'academic_enrollment_id'],
                'att_checkins_schedule_enrollment_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_checkins');
    }
};
