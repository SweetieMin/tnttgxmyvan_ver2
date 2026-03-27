<?php

namespace App\Actions\Personnel;

use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserParent;
use App\Models\UserReligiousProfile;
use App\Models\UserSetting;
use Illuminate\Support\Str;

class UpsertPersonnelProfile
{
    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, string>  $currentGroupRoleNames
     */
    public function handle(
        array $validated,
        ?User $user = null,
        ?string $storedPicture = null,
        array $currentGroupRoleNames = [],
        ?string $accountCode = null,
    ): User {
        $user ??= new User;

        $email = $validated['email'] !== '' ? $validated['email'] : null;

        $user->fill([
            'christian_name' => $validated['christianName'] !== '' ? $validated['christianName'] : null,
            'last_name' => $validated['lastName'],
            'name' => $validated['givenName'],
            'birthday' => $validated['birthday'],
            'email' => $email,
            'status_login' => $validated['statusLogin'],
        ]);

        if (! $user->exists) {
            $accountCode ??= app(GeneratePersonnelAccountCode::class)->handle($validated['birthday']);
            $user->username = $accountCode;
            $user->token = Str::random(64);
            $user->password = $accountCode;
        }

        if ($user->exists && $user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'picture' => $storedPicture,
                'bio' => $validated['bio'] !== '' ? $validated['bio'] : null,
                'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
                'address' => $validated['address'] !== '' ? $validated['address'] : null,
                'gender' => $validated['gender'],
            ],
        );

        UserParent::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'christian_name_father' => $validated['fatherChristianName'] !== '' ? $validated['fatherChristianName'] : null,
                'name_father' => $validated['fatherName'] !== '' ? $validated['fatherName'] : null,
                'phone_father' => $validated['fatherPhone'] !== '' ? $validated['fatherPhone'] : null,
                'christian_name_mother' => $validated['motherChristianName'] !== '' ? $validated['motherChristianName'] : null,
                'name_mother' => $validated['motherName'] !== '' ? $validated['motherName'] : null,
                'phone_mother' => $validated['motherPhone'] !== '' ? $validated['motherPhone'] : null,
                'christian_name_god_parent' => $validated['godParentChristianName'] !== '' ? $validated['godParentChristianName'] : null,
                'name_god_parent' => $validated['godParentName'] !== '' ? $validated['godParentName'] : null,
                'phone_god_parent' => $validated['godParentPhone'] !== '' ? $validated['godParentPhone'] : null,
            ],
        );

        UserReligiousProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'baptism_date' => $validated['baptismDate'] !== '' ? $validated['baptismDate'] : null,
                'baptism_place' => $validated['baptismPlace'] !== '' ? $validated['baptismPlace'] : null,
                'baptismal_sponsor' => $validated['baptismalSponsor'] !== '' ? $validated['baptismalSponsor'] : null,
                'first_communion_date' => $validated['firstCommunionDate'] !== '' ? $validated['firstCommunionDate'] : null,
                'first_communion_place' => $validated['firstCommunionPlace'] !== '' ? $validated['firstCommunionPlace'] : null,
                'first_communion_sponsor' => $validated['firstCommunionSponsor'] !== '' ? $validated['firstCommunionSponsor'] : null,
                'confirmation_date' => $validated['confirmationDate'] !== '' ? $validated['confirmationDate'] : null,
                'confirmation_place' => $validated['confirmationPlace'] !== '' ? $validated['confirmationPlace'] : null,
                'confirmation_bishop' => $validated['confirmationBishop'] !== '' ? $validated['confirmationBishop'] : null,
                'pledge_date' => $validated['pledgeDate'] !== '' ? $validated['pledgeDate'] : null,
                'pledge_place' => $validated['pledgePlace'] !== '' ? $validated['pledgePlace'] : null,
                'pledge_sponsor' => $validated['pledgeSponsor'] !== '' ? $validated['pledgeSponsor'] : null,
                'status_religious' => $validated['statusReligious'],
                'is_attendance' => (bool) $validated['isAttendance'],
            ],
        );

        UserSetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['lang' => $validated['lang']],
        );

        $selectedRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $validated['selectedRoleNames'])
            ->get();

        $preservedRoles = $currentGroupRoleNames === []
            ? collect()
            : $user->roles()
                ->whereNotIn('name', $currentGroupRoleNames)
                ->get();

        $user->syncRoles($preservedRoles->concat($selectedRoles)->all());

        return $user->fresh(['details', 'parents', 'religious_profile', 'settings', 'roles']);
    }
}
