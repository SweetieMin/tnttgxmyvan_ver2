<?php

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Database\Factories\SettingFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory, LogsModelActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
        'is_encrypted',
        'autoload',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'autoload' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Setting $setting): void {
            if (! $setting->is_encrypted || ! $setting->isDirty('value')) {
                return;
            }

            if ($setting->value === null || $setting->value === '') {
                return;
            }

            $setting->attributes['value'] = Crypt::encryptString($setting->value);
        });
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes): ?string {
                if ($value === null) {
                    return null;
                }

                if (! ($attributes['is_encrypted'] ?? false)) {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return $value;
                }
            },
        );
    }
}
