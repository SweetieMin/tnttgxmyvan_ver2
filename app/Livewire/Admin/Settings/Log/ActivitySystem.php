<?php

namespace App\Livewire\Admin\Settings\Log;

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Title('Nhật ký hoạt động')]
class ActivitySystem extends Component
{
    use WithPagination;

    public bool $readyToLoad = false;

    public string $search = '';

    public int $perPage = 15;

    /**
     * @var array<string, mixed>
     */
    public array $selectedActivity = [];

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

    public function openDetail(int $activityId): void
    {
        $this->ensureCanView();

        $activity = Activity::query()
            ->with('causer')
            ->findOrFail($activityId);

        $this->selectedActivity = [
            'id' => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_name' => data_get($activity->causer, 'full_name')
                ?? data_get($activity->causer, 'name')
                ?? data_get($activity->causer, 'email')
                ?? __('System'),
            'causer_id' => $activity->causer_id,
            'created_at' => $activity->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $activity->updated_at?->format('d/m/Y H:i:s'),
        ];

        $this->selectedProperties = json_encode(
            $activity->properties?->toArray() ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) ?: '{}';

        Flux::modal('activity-detail')->show();
    }

    public function getActivitiesProperty(): LengthAwarePaginator
    {
        if (! $this->readyToLoad) {
            return Activity::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }

        return Activity::query()
            ->with('causer')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search): void {
                    $builder->where('log_name', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('event', 'like', $search)
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
        abort_unless((bool) Auth::user()?->can('settings.log.activity.view'), 403);
    }

    public function render(): View
    {
        $this->ensureCanView();

        return view('livewire.admin.settings.log.activity-system');
    }
}
