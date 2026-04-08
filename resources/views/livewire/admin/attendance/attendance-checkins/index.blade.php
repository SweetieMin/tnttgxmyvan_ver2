<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Attendance check-ins')"
        :sub-title="__('Scan QR codes and review attendance records.')"
        icon="qr-code"
    />

    <div class="grid min-h-[600px] grid-cols-1 gap-4 lg:grid-cols-12">
        <div class="h-full lg:col-span-4">
            <livewire:admin.attendance.attendance-checkins.attendance-checkin-actions :attendance-schedule-id="$attendanceScheduleId" />
        </div>

        <div class="h-full lg:col-span-8">
            <div class="flex h-full flex-col gap-0 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <flux:icon.clipboard-document-check class="h-5 w-5 text-zinc-500" />
                        <flux:heading size="sm">{{ __('Checked-in list') }}</flux:heading>
                    </div>
                </div>

                <div class="flex-1 p-4">
                    <livewire:admin.attendance.attendance-checkins.attendance-checkin-list :attendance-schedule-id="$attendanceScheduleId" />
                </div>
            </div>
        </div>
    </div>
</section>
