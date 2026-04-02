<?php

namespace App\Models;

use Database\Factories\EnrollmentActivityPointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentActivityPoint extends Model
{
    /** @use HasFactory<EnrollmentActivityPointFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_enrollment_id',
        'attendance_checkin_id',
        'source_type',
        'source_id',
        'points',
        'happened_at',
        'recorded_by',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'academic_enrollment_id' => 'integer',
            'attendance_checkin_id' => 'integer',
            'source_id' => 'integer',
            'points' => 'integer',
            'happened_at' => 'datetime',
            'recorded_by' => 'integer',
        ];
    }

    public function academicEnrollment(): BelongsTo
    {
        return $this->belongsTo(AcademicEnrollment::class);
    }

    public function attendanceCheckin(): BelongsTo
    {
        return $this->belongsTo(AttendanceCheckin::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
