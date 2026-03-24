<?php

namespace App\Repositories\Eloquent;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AcademicYearRepository extends BaseRepository implements AcademicYearRepositoryInterface
{
    protected function modelClass(): string
    {
        return AcademicYear::class;
    }

    protected function logName(): string
    {
        return 'academic_years';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('status_academic', 'like', '%'.$search.'%');
            })
            ->orderByRaw("
                case status_academic
                    when 'ongoing' then 1
                    when 'upcoming' then 2
                    when 'finished' then 3
                    else 4
                end
            ")
            ->orderByDesc('catechism_start_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $academicYearId): AcademicYear
    {
        /** @var AcademicYear */
        return $this->findOrFail($academicYearId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingAcademicYearId = null, bool $syncAcademicCourses = false): AcademicYear
    {
        /** @var AcademicYear|null $subject */
        $subject = $editingAcademicYearId ? $this->find($editingAcademicYearId) : null;

        /** @var AcademicYear */
        return $this->runInTransaction(
            action: $editingAcademicYearId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingAcademicYearId, $syncAcademicCourses): AcademicYear {
                $payload = $this->normalizeAttributes($attributes);

                /** @var AcademicYear $academicYear */
                $academicYear = $editingAcademicYearId
                    ? $this->find($editingAcademicYearId)
                    : $this->create($payload);

                if ($editingAcademicYearId) {
                    /** @var AcademicYear $academicYear */
                    $academicYear = $this->update($academicYear, $payload);
                }

                if ($syncAcademicCourses) {
                    $this->syncAcademicCoursesForAcademicYear($academicYear);
                }

                return $academicYear;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'academic_year_id' => $model->getKey(),
                'academic_year_name' => $model->getAttribute('name'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeAttributes(array $attributes): array
    {
        foreach ([
            'catechism_start_date',
            'catechism_end_date',
            'activity_start_date',
            'activity_end_date',
        ] as $dateField) {
            if (($attributes[$dateField] ?? '') === '') {
                $attributes[$dateField] = null;
            }
        }

        return $attributes;
    }

    protected function syncAcademicCoursesForAcademicYear(AcademicYear $academicYear): void
    {
        $programs = Program::query()
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();

        $existingAcademicCourses = AcademicCourse::query()
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('id')
            ->get()
            ->groupBy('program_id');

        $retainedAcademicCourseIds = $programs
            ->map(function (Program $program) use ($existingAcademicCourses): ?int {
                /** @var AcademicCourse|null $academicCourse */
                $academicCourse = $existingAcademicCourses->get($program->id)?->first();

                return $academicCourse?->id;
            })
            ->filter()
            ->values()
            ->all();

        AcademicCourse::query()
            ->where('academic_year_id', $academicYear->id)
            ->when($retainedAcademicCourseIds !== [], function ($query) use ($retainedAcademicCourseIds): void {
                $query->whereNotIn('id', $retainedAcademicCourseIds);
            })
            ->when($retainedAcademicCourseIds === [], function ($query): void {
                $query->whereNotNull('id');
            })
            ->get()
            ->each(fn (AcademicCourse $academicCourse): ?bool => $academicCourse->forceDelete());

        $programs->each(function (Program $program) use ($academicYear, $existingAcademicCourses, &$retainedAcademicCourseIds): void {
            $academicCourse = $existingAcademicCourses->get($program->id)?->first();

            if ($academicCourse instanceof AcademicCourse) {
                $academicCourse->update([
                    'ordering' => $program->ordering,
                    'course_name' => $program->course,
                    'sector_name' => $program->sector,
                    'catechism_avg_score' => $academicYear->catechism_avg_score,
                    'catechism_training_score' => $academicYear->catechism_training_score,
                    'activity_score' => $academicYear->activity_score,
                    'is_active' => true,
                ]);

                $retainedAcademicCourseIds[] = $academicCourse->id;

                return;
            }

            $createdAcademicCourse = AcademicCourse::query()->create([
                'academic_year_id' => $academicYear->id,
                'program_id' => $program->id,
                'ordering' => $program->ordering,
                'course_name' => $program->course,
                'sector_name' => $program->sector,
                'catechism_avg_score' => $academicYear->catechism_avg_score,
                'catechism_training_score' => $academicYear->catechism_training_score,
                'activity_score' => $academicYear->activity_score,
                'is_active' => true,
            ]);

            $retainedAcademicCourseIds[] = $createdAcademicCourse->id;
        });

        AcademicCourse::query()
            ->where('academic_year_id', $academicYear->id)
            ->whereNotIn('id', $retainedAcademicCourseIds)
            ->delete();
    }
}
