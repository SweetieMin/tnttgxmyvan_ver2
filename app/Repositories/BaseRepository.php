<?php

namespace App\Repositories;

use App\Models\ActivityFailedLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class BaseRepository
{
    abstract protected function modelClass(): string;

    abstract protected function logName(): string;

    public function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    protected function newModel(): Model
    {
        /** @var Model $model */
        $model = app($this->modelClass());

        return $model;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @param  array<string, mixed>  $properties
     * @return TReturn
     *
     * @throws Throwable
     */
    protected function runInTransaction(
        string $action,
        callable $callback,
        ?Model $subject = null,
        array $properties = [],
        ?string $message = null,
    ): mixed {
        try {
            return DB::transaction($callback);
        } catch (Throwable $exception) {
            ActivityFailedLog::record(
                logName: $this->logName(),
                action: $action,
                subject: $subject,
                properties: $properties,
                message: $message ?? $exception->getMessage(),
                exception: $exception,
            );

            throw $exception;
        }
    }
}
