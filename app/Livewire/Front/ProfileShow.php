<?php

namespace App\Livewire\Front;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProfileShow extends Component
{
    public User $user;

    #[Url(except: 'personal')]
    public string $tab = 'personal';

    public function mount(string $token): void
    {
        $this->user = User::query()
            ->with(['details', 'parents', 'roles'])
            ->where('token', $token)
            ->firstOrFail();

        if (! in_array($this->tab, ['personal', 'parents'], true)) {
            $this->tab = 'personal';
        }
    }

    public function selectTab(string $tab): void
    {
        $this->tab = in_array($tab, ['personal', 'parents'], true) ? $tab : 'personal';
    }

    public function title(): string
    {
        return $this->user->full_name;
    }

    public function avatarUrl(): string
    {
        return (string) data_get($this->user, 'details.picture', asset('/storage/images/users/default-avatar.png'));
    }

    /**
     * @return Collection<int, string>
     */
    public function roleNames(): Collection
    {
        return $this->user->roles
            ->pluck('name')
            ->filter()
            ->values();
    }

    public function maskedPhone(?string $phone): string
    {
        $normalizedPhone = preg_replace('/\s+/u', '', trim((string) $phone));

        if ($normalizedPhone === '' || $normalizedPhone === null) {
            return '—';
        }

        if (Str::length($normalizedPhone) <= 6) {
            return $normalizedPhone;
        }

        return Str::substr($normalizedPhone, 0, 3).'******'.Str::substr($normalizedPhone, -3);
    }

    public function genderLabel(): string
    {
        return match (data_get($this->user, 'details.gender')) {
            'female' => __('Female'),
            default => __('Male'),
        };
    }

    /**
     * @return array<int, array{label: string, name: string, phone: string}>
     */
    public function parentContacts(): array
    {
        return array_values(array_filter([
            [
                'label' => __('Father'),
                'name' => trim(implode(' ', array_filter([
                    data_get($this->user, 'parents.christian_name_father'),
                    data_get($this->user, 'parents.name_father'),
                ]))),
                'phone' => $this->maskedPhone(data_get($this->user, 'parents.phone_father')),
            ],
            [
                'label' => __('Mother'),
                'name' => trim(implode(' ', array_filter([
                    data_get($this->user, 'parents.christian_name_mother'),
                    data_get($this->user, 'parents.name_mother'),
                ]))),
                'phone' => $this->maskedPhone(data_get($this->user, 'parents.phone_mother')),
            ],
            [
                'label' => __('God parent'),
                'name' => trim(implode(' ', array_filter([
                    data_get($this->user, 'parents.christian_name_god_parent'),
                    data_get($this->user, 'parents.name_god_parent'),
                ]))),
                'phone' => $this->maskedPhone(data_get($this->user, 'parents.phone_god_parent')),
            ],
        ], fn (array $contact): bool => $contact['name'] !== '' || $contact['phone'] !== '—'));
    }

    public function render(): View
    {
        return view('livewire.front.profile-show')
            ->layout('layouts.front', [
                'title' => __('Profile'),
            ]);
    }
}
