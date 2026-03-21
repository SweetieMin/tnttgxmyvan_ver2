<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    use HasFactory;
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = [
        'transaction_date',
        'transaction_item',
        'description',
        'type',
        'amount',
        'file_name',
        'in_charge',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'integer',
            'type' => 'string',
            'status' => 'string',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'income' => 'Income',
            'expense' => 'Expense',
            default => 'Income',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'income' => 'green',
            'expense' => 'rose',
            default => 'green',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            default => 'Pending',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'completed' => 'emerald',
            default => 'amber',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.').' đ';
    }

    public function getFormattedTransactionDateAttribute(): string
    {
        return $this->transaction_date?->format('d/m/Y') ?? '';
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_name) {
            return null;
        }

        return Storage::disk('public')->url($this->file_name);
    }

    public function getFileBasenameAttribute(): ?string
    {
        return $this->file_name ? basename($this->file_name) : null;
    }
}
