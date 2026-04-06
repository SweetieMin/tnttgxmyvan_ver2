@props([
    'name' => '',
    'email' => '',
    'userPicture' => null,
])

@php
    $resolvedUser = auth()->user();
    $resolvedName = $name !== '' ? $name : ($resolvedUser?->full_name ?? $resolvedUser?->name ?? '');
    $resolvedEmail = $email !== '' ? $email : ($resolvedUser?->email ?? '');
    $resolvedUserPicture = $userPicture ?? data_get($resolvedUser?->loadMissing('details'), 'details.picture');
    $impersonator = app(\Lab404\Impersonate\Services\ImpersonateManager::class)->getImpersonator();
@endphp

<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile circle
        :name="$resolvedName"
        :avatar="$resolvedUserPicture"
        icon:trailing="chevrons-up-down"
        class="[&>span]:max-lg:hidden"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar circle
                :src="$resolvedUserPicture"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ $resolvedName }}</flux:heading>
                <flux:text class="truncate">{{ $resolvedEmail }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            @if ($resolvedUser?->isImpersonated())
                <form method="POST" action="{{ route('impersonation.leave') }}" class="w-full">
                    @csrf
                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-uturn-left"
                        class="w-full cursor-pointer"
                    >
                        {{ __('Stop impersonating') }}
                        @if ($impersonator !== null)
                            ({{ $impersonator->full_name ?? $impersonator->name }})
                        @endif
                    </flux:menu.item>
                </form>

                <flux:menu.separator />
            @endif
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
