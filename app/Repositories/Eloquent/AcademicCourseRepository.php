<?php

namespace App\Repositories\Eloquent;

use App\Models\AcademicCourse;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AcademicCourseRepository extends BaseRepository implements AcademicCourseRepositoryInterface
{
    protected function modelClass(): string
    {
        return AcademicCourse::class;
    }

    protected function logName(): string
    {
        return 'academic_courses';
    }

    public function paginateForAdmin(string $search, int $perPage, ?int $academicYearId = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['program:id,course,sector'])
            ->when($academicYearId !== null, function ($query) use ($academicYearId): void {
                $query->where('academic_year_id', $academicYearId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('course_name', 'like', '%'.$search.'%')
                        ->orWhere('sector_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('ordering')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function find(int $academicCourseId): AcademicCourse
    {
        /** @var AcademicCourse */
        return $this->findOrFail($academicCourseId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingAcademicCourseId = null): AcademicCourse
    {
        /** @var AcademicCourse|null $subject */
        $subject = $editingAcademicCourseId ? $this->find($editingAcademicCourseId) : null;

        /** @var AcademicCourse */
        return $this->runInTransaction(
            action: $editingAcademicCourseId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingAcademicCourseId): AcademicCourse {
                /** @var AcademicCourse $academicCourse */
                $academicCourse = $editingAcademicCourseId
                    ? $this->find($editingAcademicCourseId)
                    : $this->create($attributes);

                if ($editingAcademicCourseId) {
                    /** @var AcademicCourse $academicCourse */
                    $academicCourse = $this->update($academicCourse, $attributes);
                }

                return $academicCourse;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'academic_course_id' => $model->getKey(),
                'course_name' => $model->getAttribute('course_name'),
                'sector_name' => $model->getAttribute('sector_name'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    public function reorder(int $academicCourseId, int $newPosition, ?int $academicYearId = null): void
    {
        /** @var AcademicCourse $subject */
        $subject = $this->find($academicCourseId);

        $this->runInTransaction(
            action: 'reorder',
            subject: $subject,
            properties: [
                'academic_course_id' => $academicCourseId,
                'academic_year_id' => $academicYearId,
                'new_position' => $newPosition,
            ],
            callback: function () use ($academicCourseId, $newPosition, $academicYearId): void {
                $academicCourses = $this->query()
                    ->when($academicYearId !== null, function ($query) use ($academicYearId): void {
                        $query->where('academic_year_id', $academicYearId);
                    })
                    ->orderBy('ordering')
                    ->orderBy('id')
                    ->get();

                /** @var AcademicCourse $academicCourse */
                $academicCourse = $academicCourses->firstWhere('id', $academicCourseId);

                if (! $academicCourse) {
                    return;
                }

                $reorderedAcademicCourses = $academicCourses
                    ->reject(fn (AcademicCourse $item): bool => $item->is($academicCourse))
                    ->values();

                $targetPosition = max(0, min($newPosition, $reorderedAcademicCourses->count()));
                $reorderedAcademicCourses->splice($targetPosition, 0, [$academicCourse]);

                $reorderedAcademicCourses->values()->each(function (AcademicCourse $item, int $index): void {
                    $item->updateQuietly([
                        'ordering' => $index + 1,
                    ]);
                });
            },
        );
    }

    public function nextOrderingForAcademicYear(int $academicYearId): int
    {
        return (int) ($this->query()
            ->where('academic_year_id', $academicYearId)
            ->max('ordering') ?? 0) + 1;
    }
}
