<?php

namespace App\Validation\Admin\Personnel;

use App\Models\User;
use Illuminate\Validation\Rule;

class UserProfileRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function pictureRules(): array
    {
        return [
            'pictureUpload' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png'],
        ];
    }

    /**
     * @param  array<int, string>  $availableRoleNames
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?int $userId, array $availableRoleNames, string $lastName, string $givenName, string $birthday): array
    {
        return [
            'selectedRoleNames' => ['required', 'array', 'min:1'],
            'selectedRoleNames.*' => ['required', 'string', Rule::in($availableRoleNames)],

            'christianName' => ['nullable', 'string', 'max:255'],
            'fullName' => ['required', 'string', 'max:255'],
            'birthday' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId, $lastName, $givenName, $birthday): void {
                    if ($lastName === '' || $givenName === '' || $birthday === '') {
                        return;
                    }

                    $query = User::withTrashed()
                        ->where('last_name', $lastName)
                        ->where('name', $givenName)
                        ->whereDate('birthday', $birthday);

                    if ($userId !== null) {
                        $query->whereKeyNot($userId);
                    }

                    if ($query->exists()) {
                        $fail(__('A user with the same full name and birthday already exists.'));
                    }
                },
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                $userId === null
                    ? Rule::unique(User::class, 'email')
                    : Rule::unique(User::class, 'email')->ignore($userId),
            ],
            'statusLogin' => ['required', Rule::in(['active', 'locked', 'inactive'])],

            ...self::pictureRules(),
            'bio' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],

            'fatherChristianName' => ['nullable', 'string', 'max:255'],
            'fatherName' => ['nullable', 'string', 'max:255'],
            'fatherPhone' => ['nullable', 'string', 'max:255'],
            'motherChristianName' => ['nullable', 'string', 'max:255'],
            'motherName' => ['nullable', 'string', 'max:255'],
            'motherPhone' => ['nullable', 'string', 'max:255'],
            'godParentChristianName' => ['nullable', 'string', 'max:255'],
            'godParentName' => ['nullable', 'string', 'max:255'],
            'godParentPhone' => ['nullable', 'string', 'max:255'],

            'baptismDate' => ['nullable', 'date'],
            'baptismPlace' => ['nullable', 'string', 'max:255'],
            'baptismalSponsor' => ['nullable', 'string', 'max:255'],
            'firstCommunionDate' => ['nullable', 'date'],
            'firstCommunionPlace' => ['nullable', 'string', 'max:255'],
            'firstCommunionSponsor' => ['nullable', 'string', 'max:255'],
            'confirmationDate' => ['nullable', 'date'],
            'confirmationPlace' => ['nullable', 'string', 'max:255'],
            'confirmationBishop' => ['nullable', 'string', 'max:255'],
            'pledgeDate' => ['nullable', 'date'],
            'pledgePlace' => ['nullable', 'string', 'max:255'],
            'pledgeSponsor' => ['nullable', 'string', 'max:255'],
            'statusReligious' => ['required', Rule::in(['in_course', 'graduated'])],
            'isAttendance' => ['boolean'],

            'lang' => ['required', Rule::in(['vi', 'en'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'selectedRoleNames.required' => __('At least one role is required.'),
            'selectedRoleNames.min' => __('At least one role is required.'),
            'selectedRoleNames.*.in' => __('The selected role is invalid for this personnel group.'),
            'fullName.required' => __('Full name is required.'),
            'birthday.required' => __('Birthday is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('This email address already exists.'),
            'statusLogin.required' => __('Status is required.'),
            'pictureUpload.image' => __('The avatar must be an image file.'),
            'pictureUpload.max' => __('The avatar may not be greater than 2048 kilobytes.'),
            'gender.required' => __('Gender is required.'),
            'statusReligious.required' => __('Study status is required.'),
            'lang.required' => __('Language is required.'),
        ];
    }
}
