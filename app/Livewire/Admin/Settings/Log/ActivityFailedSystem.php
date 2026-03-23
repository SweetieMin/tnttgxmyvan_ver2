<?php

namespace App\Livewire\Admin\Settings\Log;

use App\Models\ActivityFailedLog;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Nhật ký thao tác lỗi')]
class ActivityFailedSystem extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;

    public string $search = '';

    public int $perPage = 15;

    /**
     * @var array<string, mixed>
     */
    public array $selectedActivityFailedLog = [];

    public string $selectedProperties = '{}';

    public function loadActivities(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'perPage']);
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openDetail(int $activityFailedLogId): void
    {
        $this->ensureCanView();

        $activityFailedLog = ActivityFailedLog::query()
            ->with(['causer', 'subject'])
            ->findOrFail($activityFailedLogId);

        $this->selectedActivityFailedLog = [
            'id' => $activityFailedLog->id,
            'log_name' => $activityFailedLog->log_name,
            'action' => $activityFailedLog->action,
            'message' => $activityFailedLog->message,
            'exception' => $activityFailedLog->exception,
            'subject_type' => $activityFailedLog->subject_type,
            'subject_id' => $activityFailedLog->subject_id,
            'causer_name' => data_get($activityFailedLog->causer, 'full_name')
                ?? data_get($activityFailedLog->causer, 'name')
                ?? data_get($activityFailedLog->causer, 'email')
                ?? __('System'),
            'causer_id' => $activityFailedLog->causer_id,
            'created_at' => $activityFailedLog->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $activityFailedLog->updated_at?->format('d/m/Y H:i:s'),
        ];

        $this->selectedProperties = json_encode(
            $activityFailedLog->properties ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) ?: '{}';

        Flux::modal('activity-failed-detail')->show();
    }

    public function getActivityFailedLogsProperty(): LengthAwarePaginator
    {
        if (! $this->readyToLoad) {
            return ActivityFailedLog::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }

        return ActivityFailedLog::query()
            ->with(['causer', 'subject'])
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search): void {
                    $builder->where('log_name', 'like', $search)
                        ->orWhere('action', 'like', $search)
                        ->orWhere('message', 'like', $search)
                        ->orWhere('exception', 'like', $search)
                        ->orWhere('subject_type', 'like', $search)
                        ->orWhere('subject_id', 'like', $search)
                        ->orWhereHas('causer', function ($causerQuery) use ($search): void {
                            $causerQuery->where('name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                                ->orWhere('email', 'like', $search)
                                ->orWhere('username', 'like', $search);
                        });
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    protected function ensureCanView(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.log.activity-failed.view'), 403);
    }

    public function render(): View
    {
        $this->ensureCanView();

        return view('livewire.admin.settings.log.activity-failed-system');
    }
}
