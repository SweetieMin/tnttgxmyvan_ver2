<?php

namespace App\Models;

use Database\Factories\AttendanceCheckinFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceCheckin extends Model
{
    /** @use HasFactory<AttendanceCheckinFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attendance_schedule_id',
        'academic_enrollment_id',
        'checked_in_at',
        'checkin_method',
        'recorded_by',
        'status',
        'approved_by',
        'approved_at',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_schedule_id' => 'integer',
            'academic_enrollment_id' => 'integer',
            'checked_in_at' => 'datetime',
            'recorded_by' => 'integer',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function attendanceSchedule(): BelongsTo
    {
        return $this->belongsTo(AttendanceSchedule::class);
    }

    public function academicEnrollment(): BelongsTo
    {
        return $this->belongsTo(AcademicEnrollment::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activityPoint(): HasOne
    {
        return $this->hasOne(EnrollmentActivityPoint::class);
    }
}
