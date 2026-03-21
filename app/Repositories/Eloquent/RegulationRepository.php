<?php

namespace App\Repositories\Eloquent;

use App\Models\Regulation;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\RegulationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class RegulationRepository extends BaseRepository implements RegulationRepositoryInterface
{
    protected function modelClass(): string
    {
        return Regulation::class;
    }

    protected function logName(): string
    {
        return 'regulations';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('description', 'like', '%'.$search.'%')
                    ->orWhere('type', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
            })
            ->orderBy('ordering')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function find(int $regulationId): Regulation
    {
        /** @var Regulation */
        return $this->findOrFail($regulationId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingRegulationId = null): Regulation
    {
        /** @var Regulation|null $subject */
        $subject = $editingRegulationId ? $this->find($editingRegulationId) : null;

        /** @var Regulation */
        return $this->runInTransaction(
            action: $editingRegulationId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingRegulationId): Regulation {
                $payload = $attributes;

                if (! $editingRegulationId) {
                    $payload['ordering'] = (int) ($this->query()->max('ordering') ?? 0) + 1;
                }

                /** @var Regulation $regulation */
                $regulation = $editingRegulationId
                    ? $this->find($editingRegulationId)
                    : $this->create($payload);

                if ($editingRegulationId) {
                    /** @var Regulation $regulation */
                    $regulation = $this->update($regulation, $payload);
                }

                return $regulation;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'regulation_id' => $model->getKey(),
                'description' => $model->getAttribute('description'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    public function reorder(int $regulationId, int $newPosition): void
    {
        /** @var Regulation $subject */
        $subject = $this->find($regulationId);

        $this->runInTransaction(
            action: 'reorder',
            subject: $subject,
            properties: [
                'regulation_id' => $regulationId,
                'new_position' => $newPosition,
            ],
            callback: function () use ($regulationId, $newPosition): void {
                $regulations = $this->query()
                    ->orderBy('ordering')
                    ->orderBy('id')
                    ->get();

                /** @var Regulation $regulation */
                $regulation = $regulations->firstWhere('id', $regulationId);

                if (! $regulation) {
                    return;
                }

                $reorderedRegulations = $regulations
                    ->reject(fn (Regulation $item): bool => $item->is($regulation))
                    ->values();

                $targetPosition = max(0, min($newPosition, $reorderedRegulations->count()));
                $reorderedRegulations->splice($targetPosition, 0, [$regulation]);

                $reorderedRegulations->values()->each(function (Regulation $item, int $index): void {
                    $item->updateQuietly([
                        'ordering' => $index + 1,
                    ]);
                });
            },
        );
    }
}
