<?php

use App\Models\AttendanceSchedule;
use App\Models\Regulation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance schedules can reference a regulation while keeping their own points snapshot', function () {
    $regulation = Regulation::factory()->create([
        'description' => 'Lễ Chúa Nhật',
        'points' => 10,
    ]);

    $attendanceSchedule = AttendanceSchedule::factory()->create([
        'regulation_id' => $regulation->id,
        'points' => 15,
    ]);

    expect($attendanceSchedule->fresh()->regulation)->not->toBeNull()
        ->and($attendanceSchedule->fresh()->regulation?->is($regulation))->toBeTrue()
        ->and($attendanceSchedule->fresh()->points)->toBe(15)
        ->and($attendanceSchedule->fresh()->regulation?->points)->toBe(10);
});
