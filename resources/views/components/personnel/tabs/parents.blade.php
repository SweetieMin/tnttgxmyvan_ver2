@props([
    'mode' => 'show',
    'user' => null,
    'bindings' => [],
])

@if ($mode === 'form')
    <div class="grid gap-4 px-6 py-6 md:grid-cols-3">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Father') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['fatherChristianName'] }}" :label="__('Christian name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['fatherName'] }}" :label="__('Full name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['fatherPhone'] }}" :label="__('Phone')" />
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Mother') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['motherChristianName'] }}" :label="__('Christian name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['motherName'] }}" :label="__('Full name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['motherPhone'] }}" :label="__('Phone')" />
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Godparent') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['godParentChristianName'] }}" :label="__('Christian name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['godParentName'] }}" :label="__('Full name')" />
                <flux:input wire:model.live.debounce.300ms="{{ $bindings['godParentPhone'] }}" :label="__('Phone')" />
            </div>
        </flux:card>
    </div>
@else
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Father') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div>{{ trim((data_get($user, 'parents.christian_name_father') ?: '').' '.(data_get($user, 'parents.name_father') ?: '')) ?: '—' }}</div>
                <div class="text-zinc-500 dark:text-zinc-400">{{ data_get($user, 'parents.phone_father') ?: '—' }}</div>
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Mother') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div>{{ trim((data_get($user, 'parents.christian_name_mother') ?: '').' '.(data_get($user, 'parents.name_mother') ?: '')) ?: '—' }}</div>
                <div class="text-zinc-500 dark:text-zinc-400">{{ data_get($user, 'parents.phone_mother') ?: '—' }}</div>
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Godparent') }}</flux:heading>
            <div class="mt-4 space-y-3 text-sm">
                <div>{{ trim((data_get($user, 'parents.christian_name_god_parent') ?: '').' '.(data_get($user, 'parents.name_god_parent') ?: '')) ?: '—' }}</div>
                <div class="text-zinc-500 dark:text-zinc-400">{{ data_get($user, 'parents.phone_god_parent') ?: '—' }}</div>
            </div>
        </flux:card>
    </div>
@endif
