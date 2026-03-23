<?php

namespace App\Livewire\Admin\Finance\Categories;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Validation\Admin\Finance\CategoryRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class CategoryActions extends Component
{
    public bool $showCategoryModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingCategoryId = null;

    public ?int $deletingCategoryId = null;

    #[Validate]
    public string $name = '';

    #[Validate]
    public string $description = '';

    #[Validate]
    public bool $is_active = true;

    #[Locked]
    public array $originalCategoryState = [];

    #[On('open-create-category-modal')]
    public function openCreateModal(): void
    {
        $this->ensureCan('finance.category.create');
        $this->resetForm();
        $this->showCategoryModal = true;
    }

    #[On('edit-category')]
    public function openEditModal(int $categoryId): void
    {
        $this->ensureCan('finance.category.update');

        $category = $this->categoryRepository()->find($categoryId);

        $this->editingCategoryId = (int) $category->id;
        $this->name = $category->name;
        $this->description = (string) ($category->description ?? '');
        $this->is_active = (bool) $category->is_active;
        $this->syncOriginalCategoryState();
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->ensureCan($this->editingCategoryId ? 'finance.category.update' : 'finance.category.create');

        $validated = $this->validate();

        try {
            $this->categoryRepository()->save([
                'name' => $validated['name'],
                'description' => $validated['description'] !== '' ? $validated['description'] : null,
                'is_active' => (bool) $validated['is_active'],
            ], $this->editingCategoryId);
        } catch (Throwable $exception) {
            $this->addError('name', __('Category save failed.'));

            Flux::toast(
                text: __('Category save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: $this->editingCategoryId ? __('Category updated successfully.') : __('Category created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('category-saved');
        $this->closeCategoryModal();
    }

    #[On('confirm-delete-category')]
    public function confirmDeleteCategory(int $categoryId): void
    {
        $this->ensureCan('finance.category.delete');
        $this->deletingCategoryId = $categoryId;
        $this->showDeleteModal = true;
    }

    public function deleteCategory(): void
    {
        $this->ensureCan('finance.category.delete');

        $category = $this->categoryRepository()->find($this->deletingCategoryId);

        try {
            $this->categoryRepository()->delete($category);
        } catch (Throwable $exception) {
            $this->addError('deleteCategory', __('Category delete failed.'));

            Flux::toast(
                text: __('Category delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: __('Category deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('category-deleted');
        $this->closeDeleteModal();
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingCategoryId = null;
        $this->resetErrorBag('deleteCategory');
    }

    public function hasCategoryChanges(): bool
    {
        return $this->currentCategoryState() !== $this->originalCategoryState;
    }

    public function shouldShowSaveCategoryButton(): bool
    {
        if ($this->editingCategoryId === null) {
            return true;
        }

        return $this->hasCategoryChanges();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return CategoryRules::rules($this->editingCategoryId);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return CategoryRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingCategoryId',
            'name',
            'description',
            'is_active',
        ]);

        $this->is_active = true;
        $this->syncOriginalCategoryState();
        $this->resetErrorBag();
    }

    protected function syncOriginalCategoryState(): void
    {
        $this->originalCategoryState = $this->currentCategoryState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentCategoryState(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return app(CategoryRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.finance.categories.category-actions');
    }
}
