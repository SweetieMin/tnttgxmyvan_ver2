@props([
    'mode' => 'show',
    'user' => null,
    'bindings' => [],
    'classPlacementText' => null,
])

@if ($mode === 'form')
    <div class="grid gap-4 px-6 pb-6 md:grid-cols-2">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Study status') }}</flux:heading>
            <div class="mt-4 space-y-4">
                <flux:select wire:model.live="{{ $bindings['statusReligious'] }}" variant="listbox" :label="__('Status')">
                    <flux:select.option value="in_course">{{ __('Studying') }}</flux:select.option>
                    <flux:select.option value="graduated">{{ __('Completed') }}</flux:select.option>
                </flux:select>
                <flux:switch wire:model.live="{{ $bindings['isAttendance'] }}" :label="__('Attendance active')" />
            </div>
        </flux:card>

        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Class placement') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $classPlacementText }}
            </div>
        </flux:card>
    </div>
@else
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Current study status') }}</flux:heading>
            <div class="mt-4 flex flex-wrap gap-2">
                <flux:badge :color="data_get($user, 'religious_profile.status_religious') === 'graduated' ? 'sky' : 'violet'">
                    {{ data_get($user, 'religious_profile.status_religious') === 'graduated' ? __('Completed') : __('Studying') }}
                </flux:badge>
                <flux:badge :color="data_get($user, 'religious_profile.is_attendance', true) ? 'emerald' : 'zinc'">
                    {{ data_get($user, 'religious_profile.is_attendance', true) ? __('Attendance active') : __('Attendance inactive') }}
                </flux:badge>
            </div>
        </flux:card>
        <flux:card class="rounded-2xl p-5">
            <flux:heading size="sm">{{ __('Class placement') }}</flux:heading>
            <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $classPlacementText }}
            </div>
        </flux:card>
    </div>
@endif
