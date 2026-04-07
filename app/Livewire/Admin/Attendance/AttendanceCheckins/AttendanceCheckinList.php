<?php

namespace App\Livewire\Admin\Attendance\AttendanceCheckins;

use App\Models\AttendanceCheckin;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class AttendanceCheckinList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?int $attendanceScheduleId = null;

    #[On('checkin-recorded')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('schedule-changed')]
    public function updateSchedule(int $scheduleId): void
    {
        $this->attendanceScheduleId = $scheduleId;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AttendanceCheckin::query()
                    ->with(['academicEnrollment.user', 'academicEnrollment.academicCourse'])
                    ->when(
                        $this->attendanceScheduleId,
                        fn (Builder $query) => $query->where('attendance_schedule_id', $this->attendanceScheduleId)
                    )
                    ->when(
                        ! $this->attendanceScheduleId,
                        fn (Builder $query) => $query->whereRaw('0 = 1')
                    )
                    ->latest('checked_in_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('academicEnrollment.user.christian_full_name')
                    ->label(__('Name'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('academicEnrollment.user', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('christian_name', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('academicEnrollment.academicCourse.name')
                    ->label(__('Class'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label(__('Time'))
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('checkin_method')
                    ->label(__('Method'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'qr' => __('QR'),
                        'manual' => __('Manual'),
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'qr' => 'success',
                        'manual' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->emptyStateHeading($this->attendanceScheduleId
                ? __('No check-ins yet')
                : __('Select a schedule'))
            ->emptyStateDescription($this->attendanceScheduleId
                ? __('Scan QR codes to record attendance.')
                : __('Choose an attendance schedule to start scanning.'))
            ->emptyStateIcon($this->attendanceScheduleId ? 'heroicon-o-qr-code' : 'heroicon-o-calendar-days');
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-checkins.attendance-checkin-list');
    }
}
