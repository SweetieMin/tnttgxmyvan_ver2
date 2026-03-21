<?php

namespace App\Repositories\Eloquent;

use App\Models\Program;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\ProgramRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ProgramRepository extends BaseRepository implements ProgramRepositoryInterface
{
    protected function modelClass(): string
    {
        return Program::class;
    }

    protected function logName(): string
    {
        return 'programs';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('course', 'like', '%'.$search.'%')
                    ->orWhere('sector', 'like', '%'.$search.'%');
            })
            ->orderBy('ordering')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function find(int $programId): Program
    {
        /** @var Program */
        return $this->findOrFail($programId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingProgramId = null): Program
    {
        /** @var Program|null $subject */
        $subject = $editingProgramId ? $this->find($editingProgramId) : null;

        /** @var Program */
        return $this->runInTransaction(
            action: $editingProgramId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingProgramId): Program {
                $payload = $attributes;

                if (! $editingProgramId) {
                    $payload['ordering'] = (int) ($this->query()->max('ordering') ?? 0) + 1;
                }

                /** @var Program $program */
                $program = $editingProgramId
                    ? $this->find($editingProgramId)
                    : $this->create($payload);

                if ($editingProgramId) {
                    /** @var Program $program */
                    $program = $this->update($program, $payload);
                }

                return $program;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'program_id' => $model->getKey(),
                'program_course' => $model->getAttribute('course'),
                'program_sector' => $model->getAttribute('sector'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    public function reorder(int $programId, int $newPosition): void
    {
        /** @var Program $subject */
        $subject = $this->find($programId);

        $this->runInTransaction(
            action: 'reorder',
            subject: $subject,
            properties: [
                'program_id' => $programId,
                'new_position' => $newPosition,
            ],
            callback: function () use ($programId, $newPosition): void {
                $programs = $this->query()
                    ->orderBy('ordering')
                    ->orderBy('id')
                    ->get();

                /** @var Program $program */
                $program = $programs->firstWhere('id', $programId);

                if (! $program) {
                    return;
                }

                $reorderedPrograms = $programs
                    ->reject(fn (Program $item): bool => $item->is($program))
                    ->values();

                $targetPosition = max(0, min($newPosition, $reorderedPrograms->count()));
                $reorderedPrograms->splice($targetPosition, 0, [$program]);

                $reorderedPrograms->values()->each(function (Program $item, int $index): void {
                    $item->updateQuietly([
                        'ordering' => $index + 1,
                    ]);
                });
            },
        );
    }
}
