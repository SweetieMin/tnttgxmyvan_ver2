<?php

namespace App\Livewire\Admin\Management\Programs;

use App\Repositories\Contracts\ProgramRepositoryInterface;
use App\Validation\Admin\Management\ProgramRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class ProgramActions extends Component
{
    public bool $showProgramModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingProgramId = null;

    public ?int $deletingProgramId = null;

    #[Validate]
    public string $course = '';

    #[Validate]
    public string $sector = '';

    #[Locked]
    public array $originalProgramState = [];

    #[On('open-create-program-modal')]
    public function openCreateModal(): void
    {
        $this->ensureCan('management.program.create');
        $this->resetForm();
        $this->showProgramModal = true;
    }

    #[On('edit-program')]
    public function openEditModal(int $programId): void
    {
        $this->ensureCan('management.program.update');

        $program = $this->programRepository()->find($programId);

        $this->editingProgramId = (int) $program->id;
        $this->course = $program->course;
        $this->sector = $program->sector;
        $this->syncOriginalProgramState();
        $this->showProgramModal = true;
    }

    public function saveProgram(): void
    {
        $this->ensureCan($this->editingProgramId ? 'management.program.update' : 'management.program.create');

        $validated = $this->validate();

        try {
            $this->programRepository()->save($validated, $this->editingProgramId);
        } catch (Throwable $exception) {
            $this->addError('course', __('Program save failed.'));

            Flux::toast(
                text: __('Program save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: $this->editingProgramId ? __('Program updated successfully.') : __('Program created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('program-saved');
        $this->closeProgramModal();
    }

    #[On('confirm-delete-program')]
    public function confirmDeleteProgram(int $programId): void
    {
        $this->ensureCan('management.program.delete');
        $this->deletingProgramId = $programId;
        $this->showDeleteModal = true;
    }

    public function deleteProgram(): void
    {
        $this->ensureCan('management.program.delete');

        $program = $this->programRepository()->find($this->deletingProgramId);

        try {
            $this->programRepository()->delete($program);
        } catch (Throwable $exception) {
            $this->addError('deleteProgram', __('Program delete failed.'));

            Flux::toast(
                text: __('Program delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: __('Program deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('program-deleted');
        $this->closeDeleteModal();
    }

    public function closeProgramModal(): void
    {
        $this->showProgramModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingProgramId = null;
        $this->resetErrorBag('deleteProgram');
    }

    public function hasProgramChanges(): bool
    {
        return $this->currentProgramState() !== $this->originalProgramState;
    }

    public function shouldShowSaveProgramButton(): bool
    {
        if ($this->editingProgramId === null) {
            return true;
        }

        return $this->hasProgramChanges();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return ProgramRules::rules($this->editingProgramId);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return ProgramRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingProgramId',
            'course',
            'sector',
        ]);

        $this->syncOriginalProgramState();
        $this->resetErrorBag();
    }

    protected function syncOriginalProgramState(): void
    {
        $this->originalProgramState = $this->currentProgramState();
    }

    /**
     * @return array<string, string>
     */
    protected function currentProgramState(): array
    {
        return [
            'course' => $this->course,
            'sector' => $this->sector,
        ];
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function programRepository(): ProgramRepositoryInterface
    {
        return app(ProgramRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.management.programs.program-actions');
    }
}
