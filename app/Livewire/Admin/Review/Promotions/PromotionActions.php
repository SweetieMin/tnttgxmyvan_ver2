<?php

namespace App\Livewire\Admin\Review\Promotions;

use App\Models\AcademicEnrollment;
use App\Models\EnrollmentPromotionReview;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class PromotionActions extends Component
{
    public bool $showApprovalModal = false;

    public ?int $approvalAcademicEnrollmentId = null;

    public string $approvalNote = '';

    public ?string $approvalTargetName = null;

    #[On('open-promotion-approval-modal')]
    public function openApprovalModal(int $academicEnrollmentId): void
    {
        $this->ensureCanUpdate();

        $academicEnrollment = AcademicEnrollment::query()
            ->with('user:id,christian_name,last_name,name')
            ->whereKey($academicEnrollmentId)
            ->firstOrFail();

        if ($academicEnrollment->review_status !== 'pending_review') {
            Flux::toast(
                heading: __('Warning'),
                text: __('This child is no longer waiting for promotion review.'),
                variant: 'warning',
            );

            return;
        }

        $this->approvalAcademicEnrollmentId = $academicEnrollment->id;
        $this->approvalTargetName = $academicEnrollment->user?->christian_full_name
            ?? $academicEnrollment->user?->full_name
            ?? null;
        $this->approvalNote = '';
        $this->resetErrorBag('approvalNote');
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->approvalAcademicEnrollmentId = null;
        $this->approvalTargetName = null;
        $this->approvalNote = '';
        $this->resetErrorBag('approvalNote');
    }

    public function approvePromotion(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate([
            'approvalAcademicEnrollmentId' => ['required', 'integer'],
            'approvalNote' => ['nullable', 'string', 'max:1000'],
        ]);

        $academicEnrollment = AcademicEnrollment::query()
            ->with('user:id,christian_name,last_name,name')
            ->whereKey((int) $validated['approvalAcademicEnrollmentId'])
            ->firstOrFail();

        if ($academicEnrollment->review_status !== 'pending_review') {
            Flux::toast(
                heading: __('Warning'),
                text: __('This child is no longer waiting for promotion review.'),
                variant: 'warning',
            );

            $this->closeApprovalModal();

            return;
        }

        $reviewedAt = now();
        $reviewedBy = Auth::id();
        $approvalNote = trim($validated['approvalNote']);

        DB::transaction(function () use ($academicEnrollment, $reviewedAt, $reviewedBy, $approvalNote): void {
            $academicEnrollment->update([
                'status' => 'passed',
                'is_eligible_for_promotion' => true,
                'review_status' => 'not_required',
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => $reviewedAt,
                'review_note' => $approvalNote !== '' ? $approvalNote : null,
            ]);

            EnrollmentPromotionReview::query()->updateOrCreate(
                ['academic_enrollment_id' => $academicEnrollment->id],
                [
                    'decision' => 'promoted',
                    'reviewed_by' => $reviewedBy,
                    'reviewed_at' => $reviewedAt,
                    'note' => $approvalNote !== '' ? $approvalNote : null,
                ],
            );
        });

        $approvedChildName = $academicEnrollment->user?->christian_full_name
            ?? $academicEnrollment->user?->full_name
            ?? __('the selected child');

        $this->closeApprovalModal();
        $this->dispatch('promotion-reviewed');

        Flux::toast(
            heading: __('Success'),
            text: __('Promotion approved for :name.', [
                'name' => $approvedChildName,
            ]),
            variant: 'success',
        );
    }

    public function render(): View
    {
        return view('livewire.admin.review.promotions.promotion-actions');
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless(Auth::user()?->can('review.promotion.update') ?? false, 403);
    }
}
