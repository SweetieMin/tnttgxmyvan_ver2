<?php

namespace App\Models;

use Database\Factories\EnrollmentPromotionReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentPromotionReview extends Model
{
    /** @use HasFactory<EnrollmentPromotionReviewFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_enrollment_id',
        'decision',
        'reviewed_by',
        'reviewed_at',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'academic_enrollment_id' => 'integer',
            'reviewed_by' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function academicEnrollment(): BelongsTo
    {
        return $this->belongsTo(AcademicEnrollment::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
