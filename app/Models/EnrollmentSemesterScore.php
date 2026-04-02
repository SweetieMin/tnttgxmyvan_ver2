<?php

namespace App\Models;

use Database\Factories\EnrollmentSemesterScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentSemesterScore extends Model
{
    /** @use HasFactory<EnrollmentSemesterScoreFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_enrollment_id',
        'semester',
        'month_score_1',
        'month_score_2',
        'month_score_3',
        'month_score_4',
        'exam_score',
        'catechism_score',
        'conduct_score',
        'confirmed_by',
        'confirmed_at',
        'is_locked',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'academic_enrollment_id' => 'integer',
            'semester' => 'integer',
            'month_score_1' => 'decimal:2',
            'month_score_2' => 'decimal:2',
            'month_score_3' => 'decimal:2',
            'month_score_4' => 'decimal:2',
            'exam_score' => 'decimal:2',
            'catechism_score' => 'decimal:2',
            'conduct_score' => 'decimal:2',
            'confirmed_by' => 'integer',
            'confirmed_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function academicEnrollment(): BelongsTo
    {
        return $this->belongsTo(AcademicEnrollment::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
