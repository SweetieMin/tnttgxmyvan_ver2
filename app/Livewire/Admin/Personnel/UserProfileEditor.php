<?php

namespace App\Livewire\Admin\Personnel;

use App\Actions\Personnel\GeneratePersonnelAccountCode;
use App\Actions\Personnel\UpsertPersonnelProfile;
use App\Foundation\PersonnelDirectory;
use App\Models\User;
use App\Models\UserDetail;
use App\Validation\Admin\Personnel\UserProfileRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Nhân sự')]
class UserProfileEditor extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    protected const AVATAR_MAX_BYTES = 204800;

    protected const AVATAR_RENDER_SIZES = [600, 560, 520, 480, 440, 400, 360, 320, 280, 240, 200];

    public ?User $user = null;

    public string $group = 'users';

    public string $tab = 'personal';

    /**
     * @var array<int, string>
     */
    public array $selectedRoleNames = [];

    public string $generatedAccountCode = '';

    public string $christianName = '';

    public string $fullName = '';

    public string $birthday = '';

    public string $email = '';

    public string $statusLogin = 'active';

    public $pictureUpload = null;

    public ?string $storedPicture = null;

    public bool $showAvatarCropModal = false;

    public ?string $cropPreviewUrl = null;

    public string $croppedImageData = '';

    public string $bio = '';

    public string $phone = '';

    public string $address = '';

    public string $gender = 'male';

    public string $fatherChristianName = '';

    public string $fatherName = '';

    public string $fatherPhone = '';

    public string $motherChristianName = '';

    public string $motherName = '';

    public string $motherPhone = '';

    public string $godParentChristianName = '';

    public string $godParentName = '';

    public string $godParentPhone = '';

    public string $baptismDate = '';

    public string $baptismPlace = '';

    public string $baptismalSponsor = '';

    public string $firstCommunionDate = '';

    public string $firstCommunionPlace = '';

    public string $firstCommunionSponsor = '';

    public string $confirmationDate = '';

    public string $confirmationPlace = '';

    public string $confirmationBishop = '';

    public string $pledgeDate = '';

    public string $pledgePlace = '';

    public string $pledgeSponsor = '';

    public string $statusReligious = 'graduated';

    public bool $isAttendance = true;

    public string $lang = 'vi';

    public function mount(?string $group = null, ?User $user = null): void
    {
        if ($user !== null) {
            $this->mountForEdit($group, $user);

            return;
        }

        abort_unless(is_string($group) && $this->directory()->hasGroup($group), 404);
        abort_if($this->directory()->isDeletedUsersPage($group), 404);

        $permission = $this->directory()->createPermissionForGroup($group);
        abort_unless($permission !== null && auth()->user()?->can($permission), 403);

        $this->group = $group;
        abort_unless($this->roleOptions() !== [], 403);
        $this->selectedRoleNames = array_slice($this->roleOptions(), 0, 1);
        $this->syncDefaultStudyStatus();
    }

    public function updatedBirthday(): void
    {
        if ($this->isEditing() || $this->birthday === '') {
            return;
        }

        $this->generatedAccountCode = app(GeneratePersonnelAccountCode::class)->handle($this->birthday);
    }

    public function updatedSelectedRoleNames(): void
    {
        $this->syncDefaultStudyStatus();
    }

    public function updatedPictureUpload(): void
    {
        $this->validateOnly('pictureUpload', UserProfileRules::pictureRules());
        $this->resetErrorBag('pictureUpload');
        $this->croppedImageData = '';

        if ($this->pictureUpload !== null && method_exists($this->pictureUpload, 'temporaryUrl')) {
            $this->cropPreviewUrl = $this->pictureUpload->temporaryUrl();
            $this->showAvatarCropModal = true;
        }
    }

    public function confirmAvatarCrop(): void
    {
        if ($this->croppedImageData === '') {
            throw ValidationException::withMessages([
                'pictureUpload' => __('Please crop the avatar before saving.'),
            ]);
        }

        if ($this->isEditing()) {
            $this->persistAvatar();

            Flux::toast(
                text: __('Avatar updated successfully.'),
                heading: __('Success'),
                variant: 'success',
            );
        }

        $this->showAvatarCropModal = false;
    }

    public function cancelAvatarCrop(): void
    {
        $this->resetErrorBag('pictureUpload');
        $this->pictureUpload = null;
        $this->cropPreviewUrl = null;
        $this->croppedImageData = '';
        $this->showAvatarCropModal = false;
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function saveUserProfile(): void
    {
        [$normalizedFullName, $lastName, $givenName] = $this->splitFullName($this->fullName);

        $validated = $this->validate(
            UserProfileRules::rules(
                $this->user?->id,
                $this->roleOptions(),
                $lastName,
                $givenName,
                $this->birthday,
            ),
            UserProfileRules::messages(),
        );

        $validated['christianName'] = $this->normalizeName($validated['christianName'] ?? '');
        $validated['fullName'] = $normalizedFullName;
        $validated['lastName'] = $lastName;
        $validated['givenName'] = $givenName;

        if (! $this->isEditing() && $this->generatedAccountCode === '') {
            $this->generatedAccountCode = app(GeneratePersonnelAccountCode::class)->handle($validated['birthday']);
        }

        if ($this->pictureUpload !== null) {
            if ($this->croppedImageData === '') {
                throw ValidationException::withMessages([
                    'pictureUpload' => __('Please crop the avatar before saving.'),
                ]);
            }

            $this->storedPicture = $this->storePicture($this->accountCode());
        }

        $user = app(UpsertPersonnelProfile::class)->handle(
            $validated,
            $this->user,
            $this->storedPicture,
            $this->roleOptions(),
            $this->accountCode(),
        );

        Flux::toast(
            text: $this->isEditing()
                ? __('Personnel profile updated successfully.')
                : __('Personnel profile created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        if (! $this->isEditing()) {
            $this->redirectRoute(
                'admin.personnel.users.edit',
                [
                    'group' => $this->group,
                    'user' => $user,
                ],
                navigate: true,
            );
        }
    }

    public function title(): string
    {
        if ($this->isEditing()) {
            return __('Edit profile');
        }

        if ($this->directory()->isAllUsersPage($this->group)) {
            return __('Create personnel profile');
        }

        return __('Create :group profile', ['group' => Str::lower($this->groupLabel())]);
    }

    public function subtitle(): string
    {
        return $this->isEditing()
            ? __('Update the profile, family contacts, religious milestones, and account preferences.')
            : __('Create a new profile for this personnel page with all key information in one place.');
    }

    public function submitLabel(): string
    {
        return $this->isEditing() ? __('Save changes') : __('Create profile');
    }

    public function cancelRoute(): string
    {
        return route($this->directory()->group($this->group)['route']);
    }

    public function groupLabel(): string
    {
        return $this->directory()->group($this->group)['label'];
    }

    /**
     * @return array<int, string>
     */
    public function roleOptions(): array
    {
        return $this->directory()->manageableRoleNamesFor(
            auth()->user(),
            $this->roleOptionScope(),
        );
    }

    public function accountCode(): string
    {
        return $this->isEditing()
            ? (string) $this->user?->username
            : $this->generatedAccountCode;
    }

    public function avatarPreviewUrl(): string
    {
        if ($this->canUploadAvatar() && $this->pictureUpload !== null && method_exists($this->pictureUpload, 'temporaryUrl')) {
            if ($this->croppedImageData !== '') {
                return $this->croppedImageData;
            }

            return $this->pictureUpload->temporaryUrl();
        }

        if ($this->storedPicture !== null && $this->storedPicture !== '') {
            return asset('/storage/images/users/'.$this->storedPicture);
        }

        return asset('/storage/images/users/default-avatar.png');
    }

    public function canUploadAvatar(): bool
    {
        return $this->isEditing();
    }

    public function profileUrl(): ?string
    {
        if (! $this->isEditing() || blank($this->user?->token)) {
            return null;
        }

        return url('/profile/'.$this->user->token);
    }

    public function profileQrCodeSvg(): ?string
    {
        if (! $this->isEditing()) {
            return null;
        }

        return $this->user?->getTokenQrCode();
    }

    protected function mountForEdit(?string $group, User $user): void
    {
        abort_unless(is_string($group) && $this->directory()->hasGroup($group), 404);
        abort_if($this->directory()->isDeletedUsersPage($group), 404);

        if ($this->directory()->isAllUsersPage($group)) {
            abort_unless(
                auth()->user()?->can($this->directory()->updatePermissionForGroup('users') ?? ''),
                403,
            );
            abort_unless(
                $user->roles()->doesntExist()
                || array_intersect(
                    $user->roles()->pluck('name')->all(),
                    $this->directory()->manageableRoleNamesFor(auth()->user()),
                ) !== [],
                404,
            );
        } else {
            abort_unless(
                $this->directory()->roleNamesBelongToGroup($user->roles()->pluck('name')->all(), $group),
                404,
            );
            abort_unless(
                auth()->user()?->can($this->directory()->updatePermissionForGroup($group) ?? ''),
                403,
            );
        }

        $this->user = $user->loadMissing([
            'details',
            'parents',
            'religious_profile',
            'settings',
            'roles',
        ]);
        $this->group = $group;
        $this->selectedRoleNames = $this->user->roles
            ->pluck('name')
            ->intersect($this->roleOptions())
            ->values()
            ->all();

        $this->generatedAccountCode = (string) $this->user->username;
        $this->christianName = (string) ($this->user->christian_name ?? '');
        $this->fullName = (string) $this->user->full_name;
        $this->birthday = (string) $this->user->birthday?->format('Y-m-d');
        $this->email = (string) ($this->user->email ?? '');
        $this->statusLogin = (string) $this->user->status_login;

        $this->storedPicture = $this->user->details?->getRawOriginal('picture');
        $this->bio = (string) data_get($this->user, 'details.bio', '');
        $this->phone = (string) data_get($this->user, 'details.phone', '');
        $this->address = (string) data_get($this->user, 'details.address', '');
        $this->gender = (string) data_get($this->user, 'details.gender', 'male');

        $this->fatherChristianName = (string) data_get($this->user, 'parents.christian_name_father', '');
        $this->fatherName = (string) data_get($this->user, 'parents.name_father', '');
        $this->fatherPhone = (string) data_get($this->user, 'parents.phone_father', '');
        $this->motherChristianName = (string) data_get($this->user, 'parents.christian_name_mother', '');
        $this->motherName = (string) data_get($this->user, 'parents.name_mother', '');
        $this->motherPhone = (string) data_get($this->user, 'parents.phone_mother', '');
        $this->godParentChristianName = (string) data_get($this->user, 'parents.christian_name_god_parent', '');
        $this->godParentName = (string) data_get($this->user, 'parents.name_god_parent', '');
        $this->godParentPhone = (string) data_get($this->user, 'parents.phone_god_parent', '');

        $this->baptismDate = (string) (data_get($this->user, 'religious_profile.baptism_date')?->format('Y-m-d') ?? '');
        $this->baptismPlace = (string) data_get($this->user, 'religious_profile.baptism_place', '');
        $this->baptismalSponsor = (string) data_get($this->user, 'religious_profile.baptismal_sponsor', '');
        $this->firstCommunionDate = (string) (data_get($this->user, 'religious_profile.first_communion_date')?->format('Y-m-d') ?? '');
        $this->firstCommunionPlace = (string) data_get($this->user, 'religious_profile.first_communion_place', '');
        $this->firstCommunionSponsor = (string) data_get($this->user, 'religious_profile.first_communion_sponsor', '');
        $this->confirmationDate = (string) (data_get($this->user, 'religious_profile.confirmation_date')?->format('Y-m-d') ?? '');
        $this->confirmationPlace = (string) data_get($this->user, 'religious_profile.confirmation_place', '');
        $this->confirmationBishop = (string) data_get($this->user, 'religious_profile.confirmation_bishop', '');
        $this->pledgeDate = (string) (data_get($this->user, 'religious_profile.pledge_date')?->format('Y-m-d') ?? '');
        $this->pledgePlace = (string) data_get($this->user, 'religious_profile.pledge_place', '');
        $this->pledgeSponsor = (string) data_get($this->user, 'religious_profile.pledge_sponsor', '');
        $this->statusReligious = (string) data_get($this->user, 'religious_profile.status_religious', 'in_course');
        $this->isAttendance = (bool) data_get($this->user, 'religious_profile.is_attendance', true);

        $this->lang = (string) data_get($this->user, 'settings.lang', 'vi');
    }

    protected function isEditing(): bool
    {
        return $this->user !== null;
    }

    protected function syncDefaultStudyStatus(): void
    {
        if ($this->isEditing()) {
            return;
        }

        $this->statusReligious = in_array('Thiếu Nhi', $this->selectedRoleNames, true)
            ? 'in_course'
            : 'graduated';
    }

    protected function roleOptionScope(): ?string
    {
        return $this->directory()->isGroupPage($this->group)
            ? $this->group
            : null;
    }

    protected function storePicture(string $accountCode): string
    {
        $filename = $accountCode.'.png';
        $directory = storage_path('app/public/images/users');
        $targetPath = $directory.'/'.$filename;

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $imageContents = $this->croppedImageBinary();
        $sourceImage = imagecreatefromstring($imageContents);

        if ($sourceImage === false) {
            throw ValidationException::withMessages([
                'pictureUpload' => __('The avatar could not be processed.'),
            ]);
        }

        $optimizedPng = $this->optimizedAvatarPng($sourceImage);
        imagedestroy($sourceImage);

        file_put_contents($targetPath, $optimizedPng);

        if ($this->storedPicture !== null && $this->storedPicture !== '' && $this->storedPicture !== $filename) {
            $oldPath = $directory.'/'.$this->storedPicture;

            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        return $filename;
    }

    protected function persistAvatar(): void
    {
        if (! $this->isEditing()) {
            return;
        }

        $this->storedPicture = $this->storePicture($this->accountCode());

        UserDetail::query()->updateOrCreate(
            ['user_id' => $this->user->id],
            ['picture' => $this->storedPicture],
        );

        $this->user->unsetRelation('details');
        $this->user->load('details');

        $this->pictureUpload = null;
        $this->cropPreviewUrl = null;
        $this->croppedImageData = '';
    }

    /**
     * @param  \GdImage|resource  $sourceImage
     */
    protected function optimizedAvatarPng($sourceImage): string
    {
        $optimizedPng = '';

        foreach (self::AVATAR_RENDER_SIZES as $renderSize) {
            $canvas = imagecreatetruecolor($renderSize, $renderSize);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            imagecopyresampled(
                $canvas,
                $sourceImage,
                0,
                0,
                0,
                0,
                $renderSize,
                $renderSize,
                imagesx($sourceImage),
                imagesy($sourceImage),
            );

            ob_start();
            imagepng($canvas, null, 9);
            $candidate = ob_get_clean() ?: '';
            imagedestroy($canvas);

            if ($candidate === '') {
                continue;
            }

            $optimizedPng = $candidate;

            if (strlen($candidate) <= self::AVATAR_MAX_BYTES) {
                break;
            }
        }

        if ($optimizedPng === '') {
            throw ValidationException::withMessages([
                'pictureUpload' => __('The avatar could not be processed.'),
            ]);
        }

        return $optimizedPng;
    }

    protected function croppedImageBinary(): string
    {
        if ($this->croppedImageData === '') {
            throw ValidationException::withMessages([
                'pictureUpload' => __('Please crop the avatar before saving.'),
            ]);
        }

        $payload = Str::of($this->croppedImageData)
            ->after('base64,')
            ->value();

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw ValidationException::withMessages([
                'pictureUpload' => __('The cropped avatar data is invalid.'),
            ]);
        }

        return $decoded;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function splitFullName(string $fullName): array
    {
        $normalizedFullName = $this->normalizeName($fullName);

        if ($normalizedFullName === '') {
            return ['', '', ''];
        }

        $parts = preg_split('/\s+/u', $normalizedFullName) ?: [];
        $givenName = array_pop($parts) ?? '';
        $lastName = implode(' ', $parts);

        return [$normalizedFullName, $lastName, $givenName];
    }

    protected function normalizeName(?string $value): string
    {
        return Str::of((string) $value)
            ->squish()
            ->lower()
            ->title()
            ->value();
    }

    protected function directory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.user-profile-editor');
    }
}
