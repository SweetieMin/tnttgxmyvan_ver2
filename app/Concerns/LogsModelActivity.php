<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->getTable())
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept($this->activityLogExcludedAttributes())
            ->setDescriptionForEvent(fn (string $eventName): string => class_basename($this)." {$eventName}");
    }

    /**
     * @return array<int, string>
     */
    protected function activityLogExcludedAttributes(): array
    {
        $attributes = array_merge(
            $this->getHidden(),
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
            ],
            $this->additionalActivityLogExcludedAttributes(),
        );

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $attributes[] = $this->getDeletedAtColumn();
        }

        return array_values(array_unique(array_filter($attributes)));
    }

    /**
     * @return array<int, string>
     */
    protected function additionalActivityLogExcludedAttributes(): array
    {
        return [];
    }
}
