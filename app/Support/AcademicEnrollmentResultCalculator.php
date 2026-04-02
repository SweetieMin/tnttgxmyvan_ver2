<?php

namespace App\Support;

use App\Models\AcademicEnrollment;
use App\Models\EnrollmentSemesterScore;

class AcademicEnrollmentResultCalculator
{
    /**
     * @return array{
     *     semester_1_score: float|null,
     *     semester_2_score: float|null,
     *     final_catechism_score: float|null,
     *     final_conduct_score: float|null,
     *     final_activity_score: int,
     *     is_eligible_for_promotion: bool|null,
     *     status: string,
     *     review_status: string
     * }
     */
    public function calculate(AcademicEnrollment $academicEnrollment): array
    {
        $academicEnrollment->loadMissing([
            'academicCourse:id,catechism_avg_score,catechism_training_score,activity_score',
            'semesterScores',
            'activityPoints',
        ]);

        /** @var EnrollmentSemesterScore|null $semesterOneScore */
        $semesterOneScore = $academicEnrollment->semesterScores->firstWhere('semester', 1);
        /** @var EnrollmentSemesterScore|null $semesterTwoScore */
        $semesterTwoScore = $academicEnrollment->semesterScores->firstWhere('semester', 2);

        $semesterOneCatechismScore = $this->calculateSemesterCatechismScore($semesterOneScore);
        $semesterTwoCatechismScore = $this->calculateSemesterCatechismScore($semesterTwoScore);

        $finalCatechismScore = $semesterOneCatechismScore !== null && $semesterTwoCatechismScore !== null
            ? round(($semesterOneCatechismScore + $semesterTwoCatechismScore) / 2, 2)
            : null;

        $finalConductScore = $semesterOneScore?->conduct_score !== null && $semesterTwoScore?->conduct_score !== null
            ? round((((float) $semesterOneScore->conduct_score) + ((float) $semesterTwoScore->conduct_score)) / 2, 2)
            : null;

        $finalActivityScore = (int) $academicEnrollment->activityPoints->sum('points');

        if ($finalCatechismScore === null || $finalConductScore === null) {
            return [
                'semester_1_score' => $semesterOneCatechismScore,
                'semester_2_score' => $semesterTwoCatechismScore,
                'final_catechism_score' => $finalCatechismScore,
                'final_conduct_score' => $finalConductScore,
                'final_activity_score' => $finalActivityScore,
                'is_eligible_for_promotion' => null,
                'status' => 'studying',
                'review_status' => 'not_required',
            ];
        }

        $isEligibleForPromotion =
            $finalCatechismScore >= (float) $academicEnrollment->academicCourse->catechism_avg_score
            && $finalConductScore >= (float) $academicEnrollment->academicCourse->catechism_training_score
            && $finalActivityScore >= (int) $academicEnrollment->academicCourse->activity_score;

        return [
            'semester_1_score' => $semesterOneCatechismScore,
            'semester_2_score' => $semesterTwoCatechismScore,
            'final_catechism_score' => $finalCatechismScore,
            'final_conduct_score' => $finalConductScore,
            'final_activity_score' => $finalActivityScore,
            'is_eligible_for_promotion' => $isEligibleForPromotion,
            'status' => $isEligibleForPromotion ? 'passed' : 'pending_review',
            'review_status' => $isEligibleForPromotion ? 'not_required' : 'pending_review',
        ];
    }

    protected function calculateSemesterCatechismScore(?EnrollmentSemesterScore $semesterScore): ?float
    {
        if ($semesterScore === null) {
            return null;
        }

        $monthlyScores = [
            $semesterScore->month_score_1,
            $semesterScore->month_score_2,
            $semesterScore->month_score_3,
            $semesterScore->month_score_4,
        ];

        if (in_array(null, $monthlyScores, true) || $semesterScore->exam_score === null) {
            return null;
        }

        $monthlyTotal = array_sum(array_map(static fn (mixed $score): float => (float) $score, $monthlyScores));
        $examScore = (float) $semesterScore->exam_score;

        return round(($monthlyTotal + ($examScore * 2)) / 6, 2);
    }
}
