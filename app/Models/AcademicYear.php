<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $fillable = [
        'name',
        'catechism_start_date',
        'catechism_end_date',
        'catechism_avg_score',
        'catechism_training_score',
        'activity_start_date',
        'activity_end_date',
        'activity_score',
        'status_academic',
    ];

    protected function casts(): array
    {
        return [
            'catechism_start_date' => 'date',
            'catechism_end_date' => 'date',
            'activity_start_date' => 'date',
            'activity_end_date' => 'date',
            'catechism_avg_score' => 'decimal:2',
            'catechism_training_score' => 'decimal:2',
            'activity_score' => 'integer',
            'status_academic' => 'string',
        ];
    }

    // 🕊️ Giáo lý — Ngày bắt đầu
    public function getFormattedCatechismStartDateAttribute(): string
    {
        return optional($this->catechism_start_date)->format('Y-m-d');
    }

    // 🕊️ Giáo lý — Ngày kết thúc
    public function getFormattedCatechismEndDateAttribute(): string
    {
        return optional($this->catechism_end_date)->format('Y-m-d');
    }

    // 🕊️ Sinh hoạt — Ngày bắt đầu
    public function getFormattedActivityStartDateAttribute(): string
    {
        return optional($this->activity_start_date)->format('Y-m-d');
    }

    // 🕊️ Sinh hoạt — Ngày kết thúc
    public function getFormattedActivityEndDateAttribute(): string
    {
        return optional($this->activity_end_date)->format('Y-m-d');
    }

    public function getStatusAcademicLabelAttribute(): string
    {
        return match ($this->attributes['status_academic'] ?? null) {
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'finished' => 'Finished',
            default => 'Upcoming',
        };
    }

    public function getStatusAcademicColorAttribute(): string
    {
        return match ($this->attributes['status_academic'] ?? null) {
            'upcoming' => 'orange',
            'ongoing' => 'green',
            'finished' => 'zinc',
            default => 'orange',
        };
    }

    public function getCatechismPeriodAttribute(): string
    {
        if (! $this->catechism_start_date || ! $this->catechism_end_date) {
            return '';
        }

        return Carbon::parse($this->catechism_start_date)->format('d/m/Y').
            ' - '.
            Carbon::parse($this->catechism_end_date)->format('d/m/Y');
    }

    public function getActivityPeriodAttribute(): string
    {
        if (! $this->activity_start_date || ! $this->activity_end_date) {
            return '';
        }

        return Carbon::parse($this->activity_start_date)->format('d/m/Y').
            ' - '.
            Carbon::parse($this->activity_end_date)->format('d/m/Y');
    }
}
