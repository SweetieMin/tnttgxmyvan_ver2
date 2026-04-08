<?php

namespace App\Livewire\Admin\Review\Promotions;

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class PromotionList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('promotion-reviewed')]
    public function refreshList(): void
    {
        $this->resetTable();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->promotionTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('user.christian_full_name')
                    ->label(__('Child'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('last_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('christian_name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                    })
                    ->description(fn (AcademicEnrollment $record): string => (string) ($record->user?->username ?? ''))
                    ->wrap(),
                TextColumn::make('academicCourse.course_name')
                    ->label(__('Class'))
                    ->description(fn (AcademicEnrollment $record): string => (string) ($record->academicCourse?->sector_name ?? ''))
                    ->wrap(),
                TextColumn::make('final_catechism_score')
                    ->label(__('Catechism'))
                    ->formatStateUsing(fn (mixed $state): string => $state !== null ? number_format((float) $state, 2) : '—')
                    ->badge()
                    ->color(fn (AcademicEnrollment $record): string => $this->scoreBadgeColor($this->isBelowCatechismThreshold($record)))
                    ->alignCenter(),
                TextColumn::make('final_conduct_score')
                    ->label(__('Conduct'))
                    ->formatStateUsing(fn (mixed $state): string => $state !== null ? number_format((float) $state, 2) : '—')
                    ->badge()
                    ->color(fn (AcademicEnrollment $record): string => $this->scoreBadgeColor($this->isBelowConductThreshold($record)))
                    ->alignCenter(),
                TextColumn::make('final_activity_score')
                    ->label(__('Activity'))
                    ->formatStateUsing(fn (mixed $state): string => $state !== null ? (string) $state : '—')
                    ->badge()
                    ->color(fn (AcademicEnrollment $record): string => $this->scoreBadgeColor($this->isBelowActivityThreshold($record)))
                    ->alignCenter(),
                TextColumn::make('review_decision')
                    ->label(__('Decision'))
                    ->state(fn (AcademicEnrollment $record): string => $this->decisionLabel($record))
                    ->badge()
                    ->color(fn (AcademicEnrollment $record): string => $this->decisionColor($record)),
                TextColumn::make('promotionReview.reviewedBy.full_name')
                    ->label(__('Reviewed by'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('promotionReview.reviewed_at')
                    ->label(__('Reviewed at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('review_note')
                    ->label(__('Review note'))
                    ->state(fn (AcademicEnrollment $record): string => (string) ($record->promotionReview?->note ?? $record->review_note ?? '—'))
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label(__('Academic year'))
                    ->options($this->academicYearOptions())
                    ->default((string) $this->defaultAcademicYearId()),
                SelectFilter::make('academic_course_id')
                    ->label(__('Class filter'))
                    ->options($this->academicCourseOptions()),
                SelectFilter::make('review_scope')
                    ->label(__('Review scope'))
                    ->options([
                        'pending' => __('Pending review'),
                        'reviewed' => __('Reviewed history'),
                        'all' => __('All review records'),
                    ])
                    ->default('pending')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ((string) ($data['value'] ?? 'pending')) {
                            'reviewed' => $query->whereHas('promotionReview'),
                            'all' => $query,
                            default => $query->where('review_status', 'pending_review'),
                        };
                    }),
            ])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->deferFilters(false)
            ->stackedOnMobile()
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderByRaw("case when review_status = 'pending_review' then 0 else 1 end")
                    ->orderByRaw('coalesce((select ordering from academic_courses where academic_courses.id = academic_enrollments.academic_course_id), 99999)')
                    ->orderByDesc('reviewed_at')
                    ->orderBy('id');
            })
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No promotion records found.'))
            ->emptyStateDescription(__('Pending items appear by default. Switch the review scope filter to see confirmed decisions.'))
            ->recordActions([
                Action::make('allowPromotion')
                    ->label(__('Allow promotion'))
                    ->icon('heroicon-m-arrow-up-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->visible(fn (AcademicEnrollment $record): bool => $record->review_status === 'pending_review' && (Auth::user()?->can('review.promotion.update') ?? false))
                    ->action(function (AcademicEnrollment $record): void {
                        $this->dispatch('open-promotion-approval-modal', academicEnrollmentId: $record->getKey());
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.admin.review.promotions.promotion-list');
    }

    protected function promotionTableQuery(): Builder
    {
        return AcademicEnrollment::query()
            ->with([
                'user:id,christian_name,last_name,name,username',
                'academicCourse:id,course_name,sector_name,ordering,catechism_avg_score,catechism_training_score,activity_score',
                'promotionReview.reviewedBy:id,last_name,name',
            ])
            ->where(function (Builder $query): void {
                $query
                    ->where('review_status', 'pending_review')
                    ->orWhereHas('promotionReview');
            });
    }

    /**
     * @return array<int, string>
     */
    protected function academicYearOptions(): array
    {
        return AcademicYear::query()
            ->orderByRaw("case status_academic when 'finished' then 0 when 'ongoing' then 1 when 'upcoming' then 2 else 3 end")
            ->orderByDesc('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function academicCourseOptions(): array
    {
        $academicYearId = $this->selectedAcademicYearId();

        if ($academicYearId === null) {
            return [];
        }

        return AcademicCourse::query()
            ->where('academic_year_id', $academicYearId)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (AcademicCourse $academicCourse): array => [
                (int) $academicCourse->id => $academicCourse->course_name.' - '.$academicCourse->sector_name,
            ])
            ->all();
    }

    protected function selectedAcademicYearId(): ?int
    {
        $academicYearId = data_get($this->tableFilters, 'academic_year_id.value');

        if (is_numeric($academicYearId)) {
            return (int) $academicYearId;
        }

        return $this->defaultAcademicYearId();
    }

    protected function defaultAcademicYearId(): ?int
    {
        $academicYearIdWithPendingReview = AcademicEnrollment::query()
            ->where('review_status', 'pending_review')
            ->whereNotNull('academic_year_id')
            ->latest('academic_year_id')
            ->value('academic_year_id');

        if ($academicYearIdWithPendingReview !== null) {
            return (int) $academicYearIdWithPendingReview;
        }

        $academicYearIdWithHistory = AcademicEnrollment::query()
            ->whereHas('promotionReview')
            ->whereNotNull('academic_year_id')
            ->latest('academic_year_id')
            ->value('academic_year_id');

        if ($academicYearIdWithHistory !== null) {
            return (int) $academicYearIdWithHistory;
        }

        /** @var AcademicYear|null $academicYear */
        $academicYear = AcademicYear::query()
            ->where('status_academic', 'finished')
            ->latest('catechism_start_date')
            ->first();

        if ($academicYear !== null) {
            return (int) $academicYear->id;
        }

        $academicYearId = AcademicYear::query()->latest('id')->value('id');

        return $academicYearId !== null ? (int) $academicYearId : null;
    }

    protected function decisionLabel(AcademicEnrollment $academicEnrollment): string
    {
        if ($academicEnrollment->review_status === 'pending_review') {
            return __('Pending review');
        }

        return match ((string) $academicEnrollment->promotionReview?->decision) {
            'promoted' => __('Promoted'),
            default => __('Reviewed'),
        };
    }

    protected function decisionColor(AcademicEnrollment $academicEnrollment): string
    {
        if ($academicEnrollment->review_status === 'pending_review') {
            return 'warning';
        }

        return match ((string) $academicEnrollment->promotionReview?->decision) {
            'promoted' => 'success',
            default => 'gray',
        };
    }

    protected function isBelowCatechismThreshold(AcademicEnrollment $academicEnrollment): bool
    {
        if ($academicEnrollment->final_catechism_score === null) {
            return true;
        }

        return (float) $academicEnrollment->final_catechism_score < (float) ($academicEnrollment->academicCourse?->catechism_avg_score ?? 0);
    }

    protected function isBelowConductThreshold(AcademicEnrollment $academicEnrollment): bool
    {
        if ($academicEnrollment->final_conduct_score === null) {
            return true;
        }

        return (float) $academicEnrollment->final_conduct_score < (float) ($academicEnrollment->academicCourse?->catechism_training_score ?? 0);
    }

    protected function isBelowActivityThreshold(AcademicEnrollment $academicEnrollment): bool
    {
        if ($academicEnrollment->final_activity_score === null) {
            return true;
        }

        return (int) $academicEnrollment->final_activity_score < (int) ($academicEnrollment->academicCourse?->activity_score ?? 0);
    }

    protected function scoreBadgeColor(bool $isBelowThreshold): string
    {
        return $isBelowThreshold ? 'danger' : 'success';
    }
}
