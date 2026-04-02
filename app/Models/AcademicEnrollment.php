<?php

namespace App\Models;

use Database\Factories\AcademicEnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicEnrollment extends Model
{
    /** @use HasFactory<AcademicEnrollmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'academic_course_id',
        'status',
        'final_catechism_score',
        'final_conduct_score',
        'final_activity_score',
        'is_eligible_for_promotion',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'academic_year_id' => 'integer',
            'academic_course_id' => 'integer',
            'final_catechism_score' => 'decimal:2',
            'final_conduct_score' => 'decimal:2',
            'final_activity_score' => 'integer',
            'is_eligible_for_promotion' => 'boolean',
            'reviewed_by' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicCourse(): BelongsTo
    {
        return $this->belongsTo(AcademicCourse::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function semesterScores(): HasMany
    {
        return $this->hasMany(EnrollmentSemesterScore::class);
    }

    public function semesterOneScore(): HasOne
    {
        return $this->hasOne(EnrollmentSemesterScore::class)->where('semester', 1);
    }

    public function semesterTwoScore(): HasOne
    {
        return $this->hasOne(EnrollmentSemesterScore::class)->where('semester', 2);
    }

    public function attendanceCheckins(): HasMany
    {
        return $this->hasMany(AttendanceCheckin::class);
    }

    public function activityPoints(): HasMany
    {
        return $this->hasMany(EnrollmentActivityPoint::class);
    }

    public function promotionReview(): HasOne
    {
        return $this->hasOne(EnrollmentPromotionReview::class);
    }
}
