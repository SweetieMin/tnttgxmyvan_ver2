<?php

namespace App\Repositories\Eloquent;

use App\Models\AcademicYear;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AcademicYearRepository extends BaseRepository implements AcademicYearRepositoryInterface
{
    protected function modelClass(): string
    {
        return AcademicYear::class;
    }

    protected function logName(): string
    {
        return 'academic_years';
    }

    public function paginateForAdmin(string $search, int $perPage): LengthAwarePaginator
    {
        return $this->query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('status_academic', 'like', '%'.$search.'%');
            })
            ->orderByRaw("
                case status_academic
                    when 'ongoing' then 1
                    when 'upcoming' then 2
                    when 'finished' then 3
                    else 4
                end
            ")
            ->orderByDesc('catechism_start_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $academicYearId): AcademicYear
    {
        /** @var AcademicYear */
        return $this->findOrFail($academicYearId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingAcademicYearId = null): AcademicYear
    {
        /** @var AcademicYear|null $subject */
        $subject = $editingAcademicYearId ? $this->find($editingAcademicYearId) : null;

        /** @var AcademicYear */
        return $this->runInTransaction(
            action: $editingAcademicYearId ? 'update' : 'create',
            subject: $subject,
            properties: $attributes,
            callback: function () use ($attributes, $editingAcademicYearId): AcademicYear {
                $payload = $this->normalizeAttributes($attributes);

                /** @var AcademicYear $academicYear */
                $academicYear = $editingAcademicYearId
                    ? $this->find($editingAcademicYearId)
                    : $this->create($payload);

                if ($editingAcademicYearId) {
                    /** @var AcademicYear $academicYear */
                    $academicYear = $this->update($academicYear, $payload);
                }

                return $academicYear;
            },
        );
    }

    public function delete(Model $model): bool
    {
        return $this->runInTransaction(
            action: 'delete',
            subject: $model,
            properties: [
                'academic_year_id' => $model->getKey(),
                'academic_year_name' => $model->getAttribute('name'),
            ],
            callback: fn (): bool => parent::delete($model),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeAttributes(array $attributes): array
    {
        foreach ([
            'catechism_start_date',
            'catechism_end_date',
            'activity_start_date',
            'activity_end_date',
        ] as $dateField) {
            if (($attributes[$dateField] ?? '') === '') {
                $attributes[$dateField] = null;
            }
        }

        return $attributes;
    }
}
