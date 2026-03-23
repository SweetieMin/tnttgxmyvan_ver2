<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function logName(): string
    {
        return 'categories';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            })
            ->orderBy('ordering')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function find(int $categoryId): Category
    {
        /** @var Category */
        return $this->findOrFail($categoryId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingCategoryId = null): Category
    {
        /** @var Category|null $subject */
        $subject = $editingCategoryId ? $this->find($editingCategoryId) : null;

        /** @var Category */
        return $this->runInTransaction(
            action: $editingCategoryId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingCategoryId): Category {
                $payload = $attributes;

                if (! $editingCategoryId) {
                    $payload['ordering'] = (int) ($this->query()->max('ordering') ?? 0) + 1;
                }

                /** @var Category $category */
                $category = $editingCategoryId
                    ? $this->find($editingCategoryId)
                    : $this->create($payload);

                if ($editingCategoryId) {
                    /** @var Category $category */
                    $category = $this->update($category, $payload);
                }

                return $category;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'category_id' => $model->getKey(),
                'category_name' => $model->getAttribute('name'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    public function reorder(int $categoryId, int $newPosition): void
    {
        /** @var Category $subject */
        $subject = $this->find($categoryId);

        $this->runInTransaction(
            action: 'reorder',
            subject: $subject,
            properties: [
                'category_id' => $categoryId,
                'new_position' => $newPosition,
            ],
            callback: function () use ($categoryId, $newPosition): void {
                $categories = $this->query()
                    ->orderBy('ordering')
                    ->orderBy('id')
                    ->get();

                /** @var Category $category */
                $category = $categories->firstWhere('id', $categoryId);

                if (! $category) {
                    return;
                }

                $reorderedCategories = $categories
                    ->reject(fn (Category $item): bool => $item->is($category))
                    ->values();

                $targetPosition = max(0, min($newPosition, $reorderedCategories->count()));
                $reorderedCategories->splice($targetPosition, 0, [$category]);

                $reorderedCategories->values()->each(function (Category $item, int $index): void {
                    $item->updateQuietly([
                        'ordering' => $index + 1,
                    ]);
                });
            },
        );
    }
}
