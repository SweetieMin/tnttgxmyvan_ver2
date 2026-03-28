<?php

use App\Actions\Personnel\UpsertPersonnelProfile;
use App\Foundation\PersonnelDirectory;
use App\Livewire\Admin\Personnel\PersonnelList;
use App\Livewire\Admin\Personnel\UserProfileEditor;
use App\Models\Permission;
use App\Models\PersonnelRoleGroup;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserReligiousProfile;
use App\Validation\Admin\Personnel\UserProfileRules;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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

test('personnel directory is resolved as a singleton', function () {
    expect(app(PersonnelDirectory::class))
        ->toBe(app(PersonnelDirectory::class));
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

function largePngDataUrl(): string
{
    $canvas = imagecreatetruecolor(900, 900);

    for ($y = 0; $y < 900; $y += 10) {
        for ($x = 0; $x < 900; $x += 10) {
            $color = imagecolorallocate($canvas, ($x * 3) % 255, ($y * 5) % 255, (($x + $y) * 7) % 255);
            imagefilledrectangle($canvas, $x, $y, $x + 9, $y + 9, $color);
        }
    }

    ob_start();
    imagepng($canvas, null, 0);
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

test('all users can export a badge png from the options menu', function () {
    $avatarDirectory = storage_path('app/public/images/users');

    if (! is_dir($avatarDirectory)) {
        mkdir($avatarDirectory, 0755, true);
    }

    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view']
    );

    collect([
        'badge.layout' => [
            'type' => 'json',
            'label' => 'Badge layout',
            'value' => json_encode([
                'logo' => ['x' => 4, 'y' => 4, 'w' => 12, 'h' => 12],
                'heading' => ['x' => 16, 'y' => 3, 'w' => 72, 'h' => 15],
                'qr' => ['x' => 25, 'y' => 16, 'w' => 50, 'h' => 28],
                'name_panel' => ['x' => 3, 'y' => 74, 'w' => 94, 'h' => 20],
                'avatar' => ['x' => 12, 'y' => 45, 'w' => 76, 'h' => 40],
                'christian_name' => ['x' => 18, 'y' => 84, 'w' => 64, 'h' => 6],
                'full_name' => ['x' => 8, 'y' => 89, 'w' => 84, 'h' => 9],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ],
        'badge.title' => [
            'type' => 'string',
            'label' => 'Badge title',
            'value' => 'Đoàn TNTT Giáo Xứ Mỹ Vân',
        ],
        'badge.subtitle' => [
            'type' => 'string',
            'label' => 'Badge subtitle',
            'value' => 'Xứ đoàn Giuse Đặng Đình Viên',
        ],
    ])->each(function (array $attributes, string $key): void {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            array_merge([
                'group' => 'badge',
                'description' => null,
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 0,
            ], $attributes),
        );
    });

    $child = User::factory()->create([
        'christian_name' => 'Giuse',
        'last_name' => 'Nguyễn Khắc',
        'name' => 'Huấn',
        'token' => str_repeat('c', 64),
        'username' => 'MV12051211',
    ]);
    $child->assignRole(personnelRole('Thiếu Nhi'));
    UserDetail::query()->create([
        'user_id' => $child->id,
        'picture' => 'mv12051211-avatar.png',
    ]);

    file_put_contents(
        $avatarDirectory.'/mv12051211-avatar.png',
        base64_decode(str_replace('data:image/png;base64,', '', tinyPngDataUrl()))
    );

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'users'])
        ->call('previewBadgeUser', $child->id)
        ->assertSet('showBadgePreviewModal', true)
        ->assertSeeText(__('Badge preview'))
        ->call('exportPreviewBadgeUser')
        ->assertFileDownloaded('mv12051211-badge.png', contentType: 'image/png');
});

test('all users cannot export a badge when the avatar is missing in the database or storage', function () {
    Storage::fake('public');

    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.view']
    );

    $missingDatabaseAvatar = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Không Ảnh',
    ]);
    $missingDatabaseAvatar->assignRole(personnelRole('Thiếu Nhi'));

    $missingStorageAvatar = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Mất File',
    ]);
    $missingStorageAvatar->assignRole(personnelRole('Thiếu Nhi'));
    UserDetail::query()->create([
        'user_id' => $missingStorageAvatar->id,
        'picture' => 'missing-avatar.png',
    ]);

    $this->actingAs($viewer);

    $component = app(PersonnelList::class);
    $component->group = 'users';

    Livewire::test(PersonnelList::class, ['group' => 'users'])
        ->assertDontSeeText(__('Export badge'));

    expect($component->canExportBadgeUser($missingDatabaseAvatar))->toBeFalse()
        ->and($component->canExportBadgeUser($missingStorageAvatar))->toBeFalse();
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
        ->assertSeeText('Đội Trưởng')
        ->assertSee('env(safe-area-inset-bottom)', false);
});

test('role options are ordered by the ordering column when it exists', function () {
    if (! Schema::hasColumn('roles', 'ordering')) {
        Schema::table('roles', function (Blueprint $table): void {
            $table->unsignedInteger('ordering')->default(0);
        });
    }

    $viewer = createManagerWithManageableRoles(
        ['Đội Trưởng', 'Thiếu Nhi'],
        ['personnel.user.create']
    );

    personnelRole('Đội Trưởng')->update(['ordering' => 2]);
    personnelRole('Thiếu Nhi')->update(['ordering' => 1]);

    $this->actingAs($viewer);

    $this->get(route('admin.personnel.create', ['group' => 'users']))
        ->assertOk()
        ->assertSeeTextInOrder(['Thiếu Nhi', 'Đội Trưởng']);
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

test('confirming avatar crop saves the cropped avatar image immediately', function () {
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
        ->call('confirmAvatarCrop')
        ->assertHasNoErrors()
        ->assertSet('showAvatarCropModal', false)
        ->assertSet('cropPreviewUrl', null)
        ->assertSet('croppedImageData', '');

    $picture = $user->fresh()->details?->getRawOriginal('picture');

    expect($picture)->not->toBeNull()
        ->and($picture)->toEndWith('.png')
        ->and(file_exists(storage_path('app/public/images/users/'.$picture)))->toBeTrue();
});

test('personnel avatar upload accepts images up to 20MB', function () {
    $validator = Validator::make([
        'pictureUpload' => UploadedFile::fake()->image('large-avatar.jpg')->size(19000),
    ], UserProfileRules::pictureRules());

    expect($validator->fails())->toBeFalse();
});

test('editing a personnel profile optimizes the stored avatar size', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.update']
    );

    $user = User::factory()->create([
        'username' => 'MV12051212',
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
        ->set('pictureUpload', UploadedFile::fake()->image('avatar.jpg')->size(12000))
        ->set('croppedImageData', largePngDataUrl())
        ->call('saveUserProfile')
        ->assertHasNoErrors();

    $picture = $user->fresh()->details?->getRawOriginal('picture');
    $picturePath = storage_path('app/public/images/users/'.$picture);

    expect($picture)->not->toBeNull()
        ->and(file_exists($picturePath))->toBeTrue()
        ->and(filesize($picturePath))->toBeLessThanOrEqual(204800);
});

test('editing a personnel profile stores the avatar with a fixed account code filename and overwrites the previous file', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.user.update']
    );

    $user = User::factory()->create([
        'username' => 'MV12051212',
        'status_login' => 'active',
        'birthday' => '2012-05-12',
    ]);
    $user->assignRole(personnelRole('Thiếu Nhi'));

    $avatarDirectory = storage_path('app/public/images/users');

    if (! is_dir($avatarDirectory)) {
        mkdir($avatarDirectory, 0755, true);
    }

    file_put_contents($avatarDirectory.'/MV12051212-old.png', 'old-avatar');

    UserDetail::query()->create([
        'user_id' => $user->id,
        'gender' => 'male',
        'picture' => 'MV12051212-old.png',
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
        ->call('confirmAvatarCrop')
        ->assertHasNoErrors();

    $picture = $user->fresh()->details?->getRawOriginal('picture');
    $picturePath = $avatarDirectory.'/MV12051212.png';

    expect($picture)->toBe('MV12051212.png')
        ->and(file_exists($picturePath))->toBeTrue()
        ->and(file_exists($avatarDirectory.'/MV12051212-old.png'))->toBeFalse();
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

    $deletedUnassigned = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Chưa Phân Chức Vụ',
    ]);
    $deletedUnassigned->delete();

    $activeChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Đang Hoạt Động',
    ]);
    $activeChild->assignRole(personnelRole('Thiếu Nhi'));

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'deleted-users'])
        ->assertSeeText('Lê Đã Xoá')
        ->assertSeeText('Lê Chưa Phân Chức Vụ')
        ->assertDontSeeText('Lê Đang Hoạt Động');
});

test('deleted users can be restored from the deleted users page', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.deleted.view', 'personnel.child.update']
    );

    $deletedChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Khôi Phục',
    ]);
    $deletedChild->assignRole(personnelRole('Thiếu Nhi'));
    $deletedChild->delete();

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'deleted-users'])
        ->call('confirmRestoreUser', $deletedChild->id)
        ->call('restoreUser');

    expect($deletedChild->fresh()->trashed())->toBeFalse();
});

test('deleted users can be permanently deleted from the deleted users page', function () {
    $viewer = createManagerWithManageableRoles(
        ['Thiếu Nhi'],
        ['personnel.deleted.view', 'personnel.child.delete']
    );

    $deletedChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Xoá Hẳn',
    ]);
    $deletedChild->assignRole(personnelRole('Thiếu Nhi'));
    $deletedChild->delete();

    $this->actingAs($viewer);

    Livewire::test(PersonnelList::class, ['group' => 'deleted-users'])
        ->call('confirmForceDeleteUser', $deletedChild->id)
        ->call('forceDeleteUser');

    expect(User::withTrashed()->find($deletedChild->id))->toBeNull();
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
