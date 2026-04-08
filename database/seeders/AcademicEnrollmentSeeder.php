<?php

namespace Database\Seeders;

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class AcademicEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $children = $this->children();

        if ($children->isEmpty()) {
            return;
        }

        $ongoingAcademicYear = AcademicYear::query()
            ->where('status_academic', 'ongoing')
            ->orderByDesc('catechism_start_date')
            ->first();

        if ($ongoingAcademicYear === null) {
            return;
        }

        $previousAcademicYear = AcademicYear::query()
            ->whereDate('catechism_start_date', '<', $ongoingAcademicYear->catechism_start_date?->toDateString())
            ->orderByDesc('catechism_start_date')
            ->first();

        if ($previousAcademicYear !== null) {
            $this->seedFinishedAcademicYear($previousAcademicYear, $children);
        }

        $this->seedOngoingAcademicYear($ongoingAcademicYear, $children, $previousAcademicYear);
    }

    /**
     * @return Collection<int, User>
     */
    protected function children(): Collection
    {
        return User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'Thiếu Nhi');
            })
            ->orderBy('birthday')
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, User>  $children
     */
    protected function seedFinishedAcademicYear(AcademicYear $academicYear, Collection $children): void
    {
        $courses = $this->coursesForAcademicYear($academicYear);

        if ($courses->isEmpty()) {
            return;
        }

        $children->each(function (User $child, int $index) use ($academicYear, $courses): void {
            $course = $this->courseForIndex($courses, $index);
            $hasPassed = $index % 4 !== 0;

            AcademicEnrollment::query()->updateOrCreate(
                [
                    'user_id' => $child->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'academic_course_id' => $course->id,
                    'status' => $hasPassed ? 'passed' : 'pending_review',
                    'final_catechism_score' => $hasPassed
                        ? (float) $course->catechism_avg_score + 1.5
                        : max(0, (float) $course->catechism_avg_score - 1.0),
                    'final_conduct_score' => $hasPassed
                        ? (float) $course->catechism_training_score + 1.0
                        : max(0, (float) $course->catechism_training_score - 1.0),
                    'final_activity_score' => $hasPassed
                        ? (int) $course->activity_score + 20
                        : max(0, (int) $course->activity_score - 30),
                    'is_eligible_for_promotion' => $hasPassed,
                    'review_status' => $hasPassed ? 'not_required' : 'pending_review',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => $hasPassed ? null : 'Seeded for promotion review.',
                ],
            );
        });
    }

    /**
     * @param  Collection<int, User>  $children
     */
    protected function seedOngoingAcademicYear(
        AcademicYear $academicYear,
        Collection $children,
        ?AcademicYear $previousAcademicYear,
    ): void {
        $courses = $this->coursesForAcademicYear($academicYear);

        if ($courses->isEmpty()) {
            return;
        }

        $coursesByOrdering = $courses->keyBy('ordering');
        $previousEnrollments = $previousAcademicYear === null
            ? collect()
            : AcademicEnrollment::query()
                ->where('academic_year_id', $previousAcademicYear->id)
                ->with('academicCourse:id,ordering')
                ->get()
                ->keyBy('user_id');

        $children->each(function (User $child, int $index) use ($academicYear, $courses, $coursesByOrdering, $previousEnrollments): void {
            /** @var AcademicEnrollment|null $previousEnrollment */
            $previousEnrollment = $previousEnrollments->get($child->id);
            $course = $this->ongoingCourseForChild($courses, $coursesByOrdering, $previousEnrollment, $index);

            AcademicEnrollment::query()->updateOrCreate(
                [
                    'user_id' => $child->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'academic_course_id' => $course->id,
                    'status' => 'studying',
                    'final_catechism_score' => null,
                    'final_conduct_score' => null,
                    'final_activity_score' => null,
                    'is_eligible_for_promotion' => null,
                    'review_status' => 'not_required',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                ],
            );
        });
    }

    /**
     * @return Collection<int, AcademicCourse>
     */
    protected function coursesForAcademicYear(AcademicYear $academicYear): Collection
    {
        return AcademicCourse::query()
            ->whereBelongsTo($academicYear)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, AcademicCourse>  $courses
     */
    protected function courseForIndex(Collection $courses, int $index): AcademicCourse
    {
        /** @var AcademicCourse $course */
        $course = $courses[$index % $courses->count()];

        return $course;
    }

    /**
     * @param  Collection<int, AcademicCourse>  $courses
     * @param  Collection<int|string, AcademicCourse>  $coursesByOrdering
     */
    protected function ongoingCourseForChild(
        Collection $courses,
        Collection $coursesByOrdering,
        ?AcademicEnrollment $previousEnrollment,
        int $index,
    ): AcademicCourse {
        if ($previousEnrollment?->academicCourse === null) {
            return $this->courseForIndex($courses, $index);
        }

        $targetOrdering = $previousEnrollment->is_eligible_for_promotion
            ? ((int) $previousEnrollment->academicCourse->ordering + 1)
            : (int) $previousEnrollment->academicCourse->ordering;

        /** @var AcademicCourse|null $course */
        $course = $coursesByOrdering->get($targetOrdering);

        if ($course !== null) {
            return $course;
        }

        return $this->courseForIndex($courses, $index);
    }
}
