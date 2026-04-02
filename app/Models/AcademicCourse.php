<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Database\Factories\AcademicCourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCourse extends Model
{
    /** @use HasFactory<AcademicCourseFactory> */
    use HasFactory;

    use LogsModelActivity;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'program_id',
        'ordering',
        'course_name',
        'sector_name',
        'catechism_avg_score',
        'catechism_training_score',
        'activity_score',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'academic_year_id' => 'integer',
            'program_id' => 'integer',
            'ordering' => 'integer',
            'catechism_avg_score' => 'decimal:2',
            'catechism_training_score' => 'decimal:2',
            'activity_score' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(AcademicEnrollment::class);
    }

    public function staffAssignments(): HasMany
    {
        return $this->hasMany(AcademicCourseStaff::class);
    }
}
