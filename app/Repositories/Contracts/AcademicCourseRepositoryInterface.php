<?php

namespace App\Repositories\Contracts;

use App\Models\AcademicCourse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface AcademicCourseRepositoryInterface
{
    public function query(): Builder;

    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    public function paginateForAdmin(string $search, int $perPage, ?int $academicYearId = null): LengthAwarePaginator;

    public function find(int $academicCourseId): AcademicCourse;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function save(array $attributes, ?int $editingAcademicCourseId = null): AcademicCourse;

    public function reorder(int $academicCourseId, int $newPosition, ?int $academicYearId = null): void;

    public function nextOrderingForAcademicYear(int $academicYearId): int;
}
