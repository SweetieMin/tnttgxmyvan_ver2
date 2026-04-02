<?php

namespace Database\Factories;

use App\Models\AcademicEnrollment;
use App\Models\AttendanceCheckin;
use App\Models\AttendanceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceCheckin>
 */
class AttendanceCheckinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_schedule_id' => AttendanceSchedule::factory(),
            'academic_enrollment_id' => AcademicEnrollment::factory(),
            'checked_in_at' => now(),
            'checkin_method' => 'qr',
            'status' => 'pending',
        ];
    }
}
