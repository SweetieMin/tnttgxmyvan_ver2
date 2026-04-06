<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regulation extends Model
{
    use HasFactory;
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'ordering',
        'description',
        'short_desc',
        'type',
        'status',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'ordering' => 'integer',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->attributes['type'] ?? null) {
            'plus' => 'Bonus',
            'minus' => 'Penalty',
            default => 'Bonus',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->attributes['type'] ?? null) {
            'plus' => 'green',
            'minus' => 'rose',
            default => 'green',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->attributes['status'] ?? null) {
            'applied' => 'Applied',
            'not_applied' => 'Not applied',
            'pending' => 'Pending',
            default => 'Pending',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->attributes['status'] ?? null) {
            'applied' => 'green',
            'not_applied' => 'zinc',
            'pending' => 'amber',
            default => 'amber',
        };
    }

    public function attendanceSchedules(): HasMany
    {
        return $this->hasMany(AttendanceSchedule::class);
    }
}
