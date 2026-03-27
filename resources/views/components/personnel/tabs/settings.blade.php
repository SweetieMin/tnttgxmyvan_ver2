@props([
    'mode' => 'show',
    'user' => null,
    'bindings' => [],
])

@if ($mode === 'form')
    <div class="grid gap-4 px-6 py-6 md:grid-cols-2">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Display settings') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:select wire:model.live="{{ $bindings['lang'] }}" variant="listbox" :label="__('Preferred language')">
                    <flux:select.option value="vi">VI</flux:select.option>
                    <flux:select.option value="en">EN</flux:select.option>
                </flux:select>
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Sensitive fields') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Sensitive login data is not exposed on the profile page. Only the fields needed to manage personnel are editable here.') }}
            </div>
        </flux:card>
    </div>
@else
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Display settings') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Preferred language') }}: {{ strtoupper((string) data_get($user, 'settings.lang', 'vi')) }}
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Account status') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __('Only non-sensitive account settings are shown in this profile.') }}
            </div>
        </flux:card>
    </div>
@endif
