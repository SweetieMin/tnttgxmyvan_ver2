@props([
    'mode' => 'show',
    'user' => null,
    'roleOptions' => [],
    'bindings' => [],
    'values' => [],
])

@if ($mode === 'form')
    <div class="space-y-6 px-6 pb-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="md:col-span-1 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="{{ $bindings['christianName'] }}"
                    :label="__('Christian name')"
                />
            </div>
            <div class="md:col-span-1 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="{{ $bindings['fullName'] }}"
                    :label="__('Full name')"
                />
            </div>
            <div class="space-y-3 md:col-span-2 xl:col-span-1">
                <flux:select
                    wire:model.live="{{ $bindings['selectedRoleNames'] }}"
                    variant="listbox"
                    multiple
                    searchable
                    clearable
                    :label="__('Roles')"
                >
                    @foreach ($roleOptions as $roleName)
                        <flux:select.option :value="$roleName">{{ $roleName }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error :name="$bindings['selectedRoleNames'].'.*'" />
            </div>

            <div class="md:col-span-1 xl:col-span-1">
                <flux:date-picker
                    wire:model.live="{{ $bindings['birthday'] }}"
                    :label="__('Birthday')"

                    locale="vi-VN"
                    selectable-header
                />
            </div>
            <div class="md:col-span-1 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="{{ $bindings['address'] }}"
                    :label="__('Address')"
                />
            </div>
            <div class="md:col-span-2 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="{{ $bindings['phone'] }}"
                    :label="__('Phone')"
                />
            </div>

            <div class="md:col-span-1 xl:col-span-1">
                <flux:select
                    wire:model.live="{{ $bindings['statusLogin'] }}"
                    variant="listbox"
                    :label="__('Status')"
                >
                    <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                    <flux:select.option value="locked">{{ __('Locked') }}</flux:select.option>
                    <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                </flux:select>
            </div>
            <div class="md:col-span-1 xl:col-span-1">
                <flux:select
                    wire:model.live="{{ $bindings['gender'] }}"
                    variant="listbox"
                    :label="__('Gender')"
                >
                    <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                    <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                </flux:select>
            </div>
            <div class="md:col-span-2 xl:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="{{ $bindings['email'] }}"
                    :label="__('Email')"
                    type="email"
                />
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="md:col-span-2 xl:col-span-3">
                <flux:textarea
                    wire:model.live.debounce.300ms="{{ $bindings['bio'] }}"
                    :label="__('Personal note')"
                    class="min-h-32"
                />
            </div>
        </div>
    </div>
@else
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Basic profile') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div><span class="text-zinc-500 dark:text-zinc-400">{{ __('Christian name') }}:</span> {{ $user?->christian_name ?: '—' }}</div>
                <div><span class="text-zinc-500 dark:text-zinc-400">{{ __('Full name') }}:</span> {{ $user?->full_name ?: '—' }}</div>
                <div><span class="text-zinc-500 dark:text-zinc-400">{{ __('Address') }}:</span> {{ data_get($user, 'details.address') ?: '—' }}</div>
            </div>
        </flux:card>

        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Personal note') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ data_get($user, 'details.bio') ?: '—' }}
            </div>
        </flux:card>
    </div>
@endif
