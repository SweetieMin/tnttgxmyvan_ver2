<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ActivityFailedLog extends Model
{
    protected $fillable = [
        'log_name',
        'action',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'message',
        'exception',
        'properties',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(
        string $logName,
        string $action,
        ?EloquentModel $subject = null,
        array $properties = [],
        ?string $message = null,
        ?Throwable $exception = null,
    ): self {
        /** @var Model|null $causer */
        $causer = Auth::user();

        return static::query()->create([
            'log_name' => $logName,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'message' => $message ?? __('Action failed.'),
            'exception' => $exception ? $exception::class : null,
            'properties' => $properties,
        ]);
    }
}
