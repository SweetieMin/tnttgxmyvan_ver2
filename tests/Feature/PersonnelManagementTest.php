<?php

use App\Actions\Personnel\UpsertPersonnelProfile;
use App\Livewire\Admin\Personnel\PersonnelList;
use App\Livewire\Admin\Personnel\UserProfileEditor;
use App\Models\Permission;
use App\Models\PersonnelRoleGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserReligiousProfile;
use App\Validation\Admin\Personnel\UserProfileRules;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'personnel.user.view',
        'personnel.user.create',
        'personnel.user.update',
        'personnel.user.delete',
        'personnel.deleted.view',
        'personnel.director.view',
        'personnel.director.create',
        'personnel.director.update',
        'personnel.director.delete',
        'personnel.catechist.view',
        'personnel.catechist.create',
        'personnel.catechist.update',
        'personnel.catechist.delete',
        'personnel.leader.view',
        'personnel.leader.create',
        'personnel.leader.update',
        'personnel.leader.delete',
        'personnel.child.view',
        'personnel.child.create',
        'personnel.child.update',
        'personnel.child.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

function createManagerWithManageableRoles(array $manageableRoles, array $permissions): User
{
    $managerRole = Role::findOrCreate(fake()->unique()->slug(2), 'web');
    $managerRole->manageableRoles()->sync(
        collect($manageableRoles)
            ->map(fn (string $roleName): int => personnelRole($roleName)->id)
            ->all(),
    );

    $viewer = User::factory()->create();
    $viewer->assignRole($managerRole);
    $viewer->givePermissionTo($permissions);

    return $viewer;
}

function personnelRole(string $roleName): Role
{
    $role = Role::findOrCreate($roleName, 'web');
    $groupKeys = personnelGroupKeysForRole($roleName);

    if ($groupKeys !== []) {
        PersonnelRoleGroup::query()->where('role_id', $role->id)->delete();

        PersonnelRoleGroup::query()->insert(
            collect($groupKeys)
                ->map(fn (string $groupKey): array => [
                    'role_id' => $role->id,
                    'group_key' => $groupKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all(),
        );
    }

    return $role;
}

/**
 * @return array<int, string>
 */
function personnelGroupKeysForRole(string $roleName): array
{
    return match ($roleName) {
        'Cha Tuyên Úy', 'Thầy Phó Tế' => ['directors'],
        'Trưởng Giáo Lý', 'Phó Giáo Lý', 'Giáo Lý Viên' => ['catechists'],
        'Xứ Đoàn Trưởng',
        'Xứ Đoàn Phó',
        'Thủ Quỹ',
        'Trưởng Ngành Nghĩa',
        'Phó Ngành Nghĩa',
        'Trưởng Ngành Thiếu',
        'Phó Ngành Thiếu',
        'Trưởng Ngành Ấu',
        'Phó Ngành Ấu',
        'Trưởng Ngành Tiền Ấu',
        'Phó Ngành Tiền Ấu',
        'Huynh Trưởng',
        'Dự Trưởng' => ['leaders'],
        'Đội Trưởng' => ['leaders', 'children'],
        'Thiếu Nhi' => ['children'],
        default => [],
    };
}

function tinyPngDataUrl(): string
{
    $canvas = imagecreatetruecolor(8, 8);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);

    ob_start();
    imagepng($canvas);
    $png = ob_get_clean() ?: '';
    imagedestroy($canvas);

    return 'data:image/png;base64,'.base64_encode($png);
}

test('authorized users can visit all personnel index pages', function () {
    $viewer = User::factory()->create();
    $viewer->assignRole(Role::findOrCreate('Admin', 'web'));
    $viewer->givePermissionTo([
        'personnel.user.view',
        'personnel.deleted.view',
        'personnel.director.view',
        'personnel.catechist.view',
        'personnel.leader.view',
        'personnel.child.view',
    ]);

    $this->actingAs($viewer);

    $this->get(route('admin.personnel.users'))
        ->assertOk()
        ->assertSeeText(__('All users'));

    $this->get(route('admin.personnel.deleted-users'))
        ->assertOk()
        ->assertSeeText(__('Deleted users'));

    $this->get(route('admin.personnel.directors'))
        ->assertOk()
        ->assertSeeText(__('Directors'));

    $this->get(route('admin.personnel.catechists'))
        ->assertOk()
        ->assertSeeText(__('Catechists'));

    $this->get(route('admin.personnel.leaders'))
        ->assertOk()
        ->assertSeeText(__('Leaders'));

    $this->get(route('admin.personnel.children'))
        ->assertOk()
        ->assertSeeText(__('Children'));
});

test('all users list only shows users whose roles are manageable', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view']
    );

    $child = User::factory()->create([
        'last_name' => 'Nguyễn Khắc',
        'name' => 'Huấn',
    ]);
    $child->assignRole(personnelRole('Thiếu Nhi'));

    $director = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Linh Hướng',
    ]);
    $director->assignRole(personnelRole('Cha Tuyên Úy'));

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'users'])
        ->assertSeeText('Nguyễn Khắc Huấn')
        ->assertDontSeeText('Trần Linh Hướng');
});

test('all users list still shows users without any assigned role', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view']
    );

    $unassignedUser = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Mai',
    ]);

    $director = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Linh Hướng',
    ]);
    $director->assignRole(personnelRole('Cha Tuyên Úy'));

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'users'])
        ->assertSeeText($unassignedUser->full_name)
        ->assertDontSeeText($director->full_name);
});

test('all users edit page is accessible for an unassigned user', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view', 'personnel.user.update']
    );

    $unassignedUser = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Mai',
    ]);

    $this->actingAs($viewer);

    $this->get(route('admin.personnel.users.edit', [
        'group' => 'users',
        'user' => $unassignedUser,
    ]))->assertOk();
});

test('all users list sorts by given name before last name', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view']
    );

    $third = User::factory()->create(['last_name' => 'Phạm', 'name' => 'Lan']);
    $third->assignRole(personnelRole('Thiếu Nhi'));

    $first = User::factory()->create(['last_name' => 'Nguyễn Khắc', 'name' => 'Huấn']);
    $first->assignRole(personnelRole('Thiếu Nhi'));

    $second = User::factory()->create(['last_name' => 'Trần', 'name' => 'Kỳ']);
    $second->assignRole(personnelRole('Thiếu Nhi'));

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'users'])
        ->assertSeeTextInOrder([
            'Nguyễn Khắc Huấn',
            'Trần Kỳ',
            'Phạm Lan',
        ]);
});

test('group personnel list only shows roles that belong to the current group context', function () {
    $viewer = createManagerWithManageableRoles(
        ['Giáo Lý Viên', 'Huynh Trưởng'],
        ['personnel.leader.view']
    );

    $user = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'An',
    ]);
    $user->assignRole([
        personnelRole('Giáo Lý Viên'),
        personnelRole('Huynh Trưởng'),
    ]);

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'leaders'])
        ->assertSeeText('Huynh Trưởng')
        ->assertDontSeeText('Giáo Lý Viên');
});

test('create pages only offer manageable roles for the current context', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi', 'Đội Trưởng'],
        ['personnel.user.create', 'personnel.child.create']
    );

    $this->actingAs($viewer);

    $this->get(route('admin.personnel.create', ['group' => 'users']))
        ->assertOk()
        ->assertSeeText(__('Create personnel profile'))
        ->assertSeeText('Thiếu Nhi')
        ->assertSeeText('Đội Trưởng')
        ->assertDontSeeText('Cha Tuyên Úy');

    $this->get(route('admin.personnel.create', ['group' => 'children']))
        ->assertOk()
        ->assertSeeText(__('Create :group profile', ['group' => 'thiếu nhi']))
        ->assertSeeText('Thiếu Nhi')
        ->assertSeeText('Đội Trưởng');
});

test('creating a user auto generates account code token and default password', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi', 'Đội Trưởng'],
        ['personnel.user.create', 'personnel.user.view']
    );

    $this->actingAs($viewer);

    app(UpsertPersonnelProfile::class)->handle([
        'selectedRoleNames' => ['Thiếu Nhi', 'Đội Trưởng'],
        'christianName' => '',
        'lastName' => 'Trần Văn',
        'givenName' => 'Bình',
        'birthday' => '2012-05-12',
        'email' => '',
        'statusLogin' => 'active',
        'bio' => '',
        'phone' => '0909000022',
        'address' => '',
        'gender' => 'male',
        'fatherChristianName' => '',
        'fatherName' => '',
        'fatherPhone' => '',
        'motherChristianName' => '',
        'motherName' => '',
        'motherPhone' => '',
        'godParentChristianName' => '',
        'godParentName' => '',
        'godParentPhone' => '',
        'baptismDate' => '',
        'baptismPlace' => '',
        'baptismalSponsor' => '',
        'firstCommunionDate' => '',
        'firstCommunionPlace' => '',
        'firstCommunionSponsor' => '',
        'confirmationDate' => '',
        'confirmationPlace' => '',
        'confirmationBishop' => '',
        'pledgeDate' => '',
        'pledgePlace' => '',
        'pledgeSponsor' => '',
        'statusReligious' => 'in_course',
        'isAttendance' => true,
        'lang' => 'vi',
    ], null, null, ['Thiếu Nhi', 'Đội Trưởng']);

    $createdUser = User::query()
        ->where('last_name', 'Trần Văn')
        ->where('name', 'Bình')
        ->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser?->username)->toMatch('/^MV120512\d{2}$/')
        ->and(strlen((string) $createdUser?->token))->toBe(64)
        ->and(Hash::check((string) $createdUser?->username, (string) $createdUser?->password))->toBeTrue()
        ->and($createdUser?->roles->pluck('name')->all())->toMatchArray(['Thiếu Nhi', 'Đội Trưởng'])
        ->and(data_get($createdUser?->details, 'phone'))->toBe('0909000022');
});

test('editing a personnel profile stores the cropped avatar image', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.update']
    );

    $user = User::factory()->create([
        'username' => 'MV12051211',
        'status_login' => 'active',
        'birthday' => '2012-05-12',
    ]);
    $user->assignRole(personnelRole('Thiếu Nhi'));
    UserDetail::query()->create([
        'user_id' => $user->id,
        'gender' => 'male',
    ]);

    $this->actingAs($viewer);

    Livewire::test(UserProfileEditor::class, ['group' => 'users', 'user' => $user])
        ->set('selectedRoleNames', ['Thiếu Nhi'])
        ->set('fullName', $user->full_name)
        ->set('birthday', $user->birthday->format('Y-m-d'))
        ->set('statusLogin', 'active')
        ->set('gender', 'male')
        ->set('statusReligious', 'in_course')
        ->set('lang', 'vi')
        ->set('pictureUpload', UploadedFile::fake()->image('avatar.jpg'))
        ->set('croppedImageData', tinyPngDataUrl())
        ->call('saveUserProfile')
        ->assertHasNoErrors();

    $picture = $user->fresh()->details?->getRawOriginal('picture');

    expect($picture)->not->toBeNull()
        ->and($picture)->toEndWith('.png')
        ->and(file_exists(storage_path('app/public/images/users/'.$picture)))->toBeTrue();
});

test('editing a personnel profile does not redirect after saving', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.update']
    );

    $user = User::factory()->create([
        'username' => 'MV12051211',
        'status_login' => 'active',
        'birthday' => '2012-05-12',
    ]);
    $user->assignRole(personnelRole('Thiếu Nhi'));
    UserDetail::query()->create([
        'user_id' => $user->id,
        'gender' => 'male',
    ]);

    $this->actingAs($viewer);

    Livewire::test(UserProfileEditor::class, ['group' => 'users', 'user' => $user])
        ->set('selectedRoleNames', ['Thiếu Nhi'])
        ->set('fullName', $user->full_name)
        ->set('birthday', $user->birthday->format('Y-m-d'))
        ->set('statusLogin', 'active')
        ->set('gender', 'male')
        ->set('statusReligious', 'in_course')
        ->set('lang', 'vi')
        ->call('saveUserProfile')
        ->assertHasNoErrors()
        ->assertNoRedirect();
});

test('editor full name helper normalizes and splits the saved name parts', function () {
    $editor = new class extends UserProfileEditor
    {
        /**
         * @return array{0: string, 1: string, 2: string}
         */
        public function splitForTest(string $fullName): array
        {
            return $this->splitFullName($fullName);
        }

        public function normalizeForTest(string $value): string
        {
            return $this->normalizeName($value);
        }
    };

    expect($editor->normalizeForTest('  giuse '))->toBe('Giuse')
        ->and($editor->splitForTest('  nguyễn   khắc   huấn  '))
        ->toBe(['Nguyễn Khắc Huấn', 'Nguyễn Khắc', 'Huấn']);
});

test('duplicate full name and birthday is blocked even when the previous user is soft deleted', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.create']
    );

    $deletedUser = User::factory()->create([
        'last_name' => 'Nguyễn Khắc',
        'name' => 'Huấn',
        'birthday' => '2013-01-01',
    ]);
    $deletedUser->assignRole(personnelRole('Thiếu Nhi'));
    $deletedUser->delete();

    $validator = Validator::make([
        'selectedRoleNames' => ['Thiếu Nhi'],
        'christianName' => '',
        'fullName' => 'Nguyễn Khắc Huấn',
        'birthday' => '2013-01-01',
        'email' => '',
        'statusLogin' => 'active',
        'bio' => '',
        'phone' => '',
        'address' => '',
        'gender' => 'male',
        'fatherChristianName' => '',
        'fatherName' => '',
        'fatherPhone' => '',
        'motherChristianName' => '',
        'motherName' => '',
        'motherPhone' => '',
        'godParentChristianName' => '',
        'godParentName' => '',
        'godParentPhone' => '',
        'baptismDate' => '',
        'baptismPlace' => '',
        'baptismalSponsor' => '',
        'firstCommunionDate' => '',
        'firstCommunionPlace' => '',
        'firstCommunionSponsor' => '',
        'confirmationDate' => '',
        'confirmationPlace' => '',
        'confirmationBishop' => '',
        'pledgeDate' => '',
        'pledgePlace' => '',
        'pledgeSponsor' => '',
        'statusReligious' => 'in_course',
        'isAttendance' => true,
        'lang' => 'vi',
    ], UserProfileRules::rules(
        null,
        ['Thiếu Nhi'],
        'Nguyễn Khắc',
        'Huấn',
        '2013-01-01',
    ));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('birthday'))->toBeTrue();
});

test('editing one personnel group preserves roles from another group', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.child.update']
    );

    $childRole = personnelRole('Thiếu Nhi');
    $leaderRole = personnelRole('Đội Trưởng');

    $user = User::factory()->create([
        'username' => 'MV22334455',
        'status_login' => 'active',
    ]);
    $user->assignRole([$childRole, $leaderRole]);

    app(UpsertPersonnelProfile::class)->handle([
        'selectedRoleNames' => ['Thiếu Nhi'],
        'christianName' => '',
        'lastName' => $user->last_name,
        'givenName' => $user->name,
        'birthday' => $user->birthday->format('Y-m-d'),
        'email' => (string) ($user->email ?? ''),
        'statusLogin' => (string) $user->status_login,
        'bio' => '',
        'phone' => '',
        'address' => '',
        'gender' => 'male',
        'fatherChristianName' => '',
        'fatherName' => '',
        'fatherPhone' => '',
        'motherChristianName' => '',
        'motherName' => '',
        'motherPhone' => '',
        'godParentChristianName' => '',
        'godParentName' => '',
        'godParentPhone' => '',
        'baptismDate' => '',
        'baptismPlace' => '',
        'baptismalSponsor' => '',
        'firstCommunionDate' => '',
        'firstCommunionPlace' => '',
        'firstCommunionSponsor' => '',
        'confirmationDate' => '',
        'confirmationPlace' => '',
        'confirmationBishop' => '',
        'pledgeDate' => '',
        'pledgePlace' => '',
        'pledgeSponsor' => '',
        'statusReligious' => 'in_course',
        'isAttendance' => true,
        'lang' => 'vi',
    ], $user, null, ['Thiếu Nhi']);

    expect($user->fresh()->roles->pluck('name')->all())
        ->toContain('Thiếu Nhi')
        ->toContain('Đội Trưởng');
});

test('deleted users page lists only soft deleted manageable users', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.deleted.view']
    );

    $deletedChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Đã Xoá',
    ]);
    $deletedChild->assignRole(personnelRole('Thiếu Nhi'));
    $deletedChild->delete();

    $activeChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Đang Hoạt Động',
    ]);
    $activeChild->assignRole(personnelRole('Thiếu Nhi'));

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'deleted-users'])
        ->assertSeeText('Lê Đã Xoá')
        ->assertDontSeeText('Lê Đang Hoạt Động');
});

test('legacy personnel show url redirects to edit page', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.update']
    );

    $user = User::factory()->create([
        'christian_name' => 'Phêrô',
        'last_name' => 'Nguyễn',
        'name' => 'An',
        'username' => 'MV00000001',
        'birthday' => '2010-08-21',
    ]);
    $user->assignRole(personnelRole('Thiếu Nhi'));

    UserDetail::query()->create([
        'user_id' => $user->id,
        'phone' => '0909000001',
        'gender' => 'male',
        'address' => 'Giáo xứ Mỹ Vân',
    ]);

    UserReligiousProfile::query()->create([
        'user_id' => $user->id,
        'status_religious' => 'in_course',
        'is_attendance' => true,
    ]);

    $this->actingAs($viewer)
        ->get('/admin/personnel/users/users/'.$user->id)
        ->assertRedirect(route('admin.personnel.users.edit', ['group' => 'users', 'user' => $user]));
});
