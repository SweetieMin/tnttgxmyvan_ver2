<div class="flex h-full flex-col gap-0 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <flux:icon.video-camera class="h-5 w-5 text-zinc-500" />
            <flux:heading size="sm">{{ __('QR Scanner') }}</flux:heading>
        </div>
    </div>

    <div class="flex-1 p-4">
        <livewire:admin.attendance.attendance-checkins.attendance-checkin-scanner :attendance-schedule-id="$attendanceScheduleId" />
    </div>
</div>
