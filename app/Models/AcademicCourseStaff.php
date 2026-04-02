<?php

namespace App\Models;

use Database\Factories\AcademicCourseStaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicCourseStaff extends Model
{
    /** @use HasFactory<AcademicCourseStaffFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_course_id',
        'user_id',
        'assignment_type',
        'is_primary',
        'assigned_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'academic_course_id' => 'integer',
            'user_id' => 'integer',
            'is_primary' => 'boolean',
            'assigned_by' => 'integer',
        ];
    }

    public function academicCourse(): BelongsTo
    {
        return $this->belongsTo(AcademicCourse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
