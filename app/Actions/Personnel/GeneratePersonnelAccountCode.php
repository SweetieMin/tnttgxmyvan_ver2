<?php

namespace App\Actions\Personnel;

use App\Models\User;
use Carbon\CarbonImmutable;

class GeneratePersonnelAccountCode
{
    public function handle(string $birthday): string
    {
        $prefix = 'MV'.CarbonImmutable::parse($birthday)->format('dmy');
        $suffixes = range(0, 99);
        shuffle($suffixes);

        foreach ($suffixes as $suffix) {
            $accountCode = $prefix.str_pad((string) $suffix, 2, '0', STR_PAD_LEFT);

            if (! User::withTrashed()->where('username', $accountCode)->exists()) {
                return $accountCode;
            }
        }

        abort(422, __('Unable to generate a unique profile code from this birthday.'));
    }
}
