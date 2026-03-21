<?php

namespace App\Livewire\Admin\Management\Regulations;

use App\Repositories\Contracts\RegulationRepositoryInterface;
use App\Validation\Admin\Management\RegulationRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class RegulationActions extends Component
{
    public bool $showRegulationModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingRegulationId = null;

    public ?int $deletingRegulationId = null;

    #[Validate]
    public string $description = '';

    #[Validate]
    public string $type = 'plus';

    #[Validate]
    public string $status = 'pending';

    #[Validate]
    public int|string $points = 0;

    #[Locked]
    public array $originalRegulationState = [];

    #[On('open-create-regulation-modal')]
    public function openCreateModal(): void
    {
        $this->ensureCan('management.regulation.create');
        $this->resetForm();
        $this->showRegulationModal = true;
    }

    #[On('edit-regulation')]
    public function openEditModal(int $regulationId): void
    {
        $this->ensureCan('management.regulation.update');

        $regulation = $this->regulationRepository()->find($regulationId);

        $this->editingRegulationId = (int) $regulation->id;
        $this->description = $regulation->description;
        $this->type = $regulation->type;
        $this->status = $regulation->status;
        $this->points = (int) $regulation->points;
        $this->syncOriginalRegulationState();
        $this->showRegulationModal = true;
    }

    public function saveRegulation(): void
    {
        $this->ensureCan($this->editingRegulationId ? 'management.regulation.update' : 'management.regulation.create');

        $validated = $this->validate();

        try {
            $this->regulationRepository()->save($validated, $this->editingRegulationId);
        } catch (Throwable $exception) {
            $this->addError('description', __('Regulation save failed.'));

            Flux::toast(
                text: __('Regulation save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: $this->editingRegulationId ? __('Regulation updated successfully.') : __('Regulation created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('regulation-saved');
        $this->closeRegulationModal();
    }

    #[On('confirm-delete-regulation')]
    public function confirmDeleteRegulation(int $regulationId): void
    {
        $this->ensureCan('management.regulation.delete');
        $this->deletingRegulationId = $regulationId;
        $this->showDeleteModal = true;
    }

    public function deleteRegulation(): void
    {
        $this->ensureCan('management.regulation.delete');

        $regulation = $this->regulationRepository()->find($this->deletingRegulationId);

        try {
            $this->regulationRepository()->delete($regulation);
        } catch (Throwable $exception) {
            $this->addError('deleteRegulation', __('Regulation delete failed.'));

            Flux::toast(
                text: __('Regulation delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: __('Regulation deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('regulation-deleted');
        $this->closeDeleteModal();
    }

    public function closeRegulationModal(): void
    {
        $this->showRegulationModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingRegulationId = null;
        $this->resetErrorBag('deleteRegulation');
    }

    public function hasRegulationChanges(): bool
    {
        return $this->currentRegulationState() !== $this->originalRegulationState;
    }

    public function shouldShowSaveRegulationButton(): bool
    {
        if ($this->editingRegulationId === null) {
            return true;
        }

        return $this->hasRegulationChanges();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return RegulationRules::rules();
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return RegulationRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingRegulationId',
            'description',
            'type',
            'status',
            'points',
        ]);

        $this->type = 'plus';
        $this->status = 'pending';
        $this->points = 0;
        $this->syncOriginalRegulationState();
        $this->resetErrorBag();
    }

    protected function syncOriginalRegulationState(): void
    {
        $this->originalRegulationState = $this->currentRegulationState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentRegulationState(): array
    {
        return [
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'points' => (int) $this->points,
        ];
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function regulationRepository(): RegulationRepositoryInterface
    {
        return app(RegulationRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.management.regulations.regulation-actions');
    }
}
