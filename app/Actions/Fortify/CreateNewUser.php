<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        ['last_name' => $lastName, 'name' => $name] = $this->splitFullName($input['name']);

        Validator::make($input, [
            ...$this->profileRules(),
            'username' => $this->usernameRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'last_name' => $lastName,
            'name' => $name,
            'birthday' => now()->toDateString(),
            'username' => $input['username'],
            'email' => $input['email'],
            'token' => Str::random(60),
            'password' => $input['password'],
        ]);
    }

    /**
     * @return array{last_name: string, name: string}
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $name = array_pop($parts) ?? $fullName;
        $lastName = implode(' ', $parts);

        return [
            'last_name' => $lastName !== '' ? $lastName : $name,
            'name' => $name,
        ];
    }
}
