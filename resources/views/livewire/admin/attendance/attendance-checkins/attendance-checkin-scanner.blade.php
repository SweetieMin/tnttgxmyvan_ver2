@php $schedule = $this->currentSchedule(); @endphp
@php
    $scheduleEndsAt = $schedule
        ? $schedule->attendance_date?->format('Y-m-d').'T'.substr((string) $schedule->end_time, 0, 8).'+07:00'
        : null;
@endphp

<div
    class="flex h-full flex-col gap-4"
    x-data="qrScanner(@js($scheduleEndsAt))"
    x-init="init()"
>

    {{-- Schedule info banner --}}
    @if ($schedule)
        <div
            class="rounded-xl border px-4 py-3"
            :class="canScan
                ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/50 dark:bg-emerald-950/30'
                : 'border-amber-200 bg-amber-50 dark:border-amber-800/50 dark:bg-amber-950/30'"
        >
            <div class="flex items-start gap-3">
                <flux:icon.calendar-days
                    class="mt-0.5 h-5 w-5 shrink-0"
                    x-bind:class="canScan
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : 'text-amber-600 dark:text-amber-400'"
                />
                <div class="min-w-0 flex-1">
                    <flux:text
                        class="font-semibold"
                        x-bind:class="canScan
                            ? 'text-emerald-800 dark:text-emerald-300'
                            : 'text-amber-800 dark:text-amber-300'"
                    >
                        {{ $schedule->title }}
                    </flux:text>
                    <flux:text
                        class="mt-0.5 text-xs"
                        x-bind:class="canScan
                            ? 'text-emerald-600 dark:text-emerald-500'
                            : 'text-amber-600 dark:text-amber-500'"
                    >
                        {{ $schedule->attendance_date?->format('d/m/Y') }}
                        &middot;
                        {{ substr((string) $schedule->start_time, 0, 5) }} – {{ substr((string) $schedule->end_time, 0, 5) }}
                    </flux:text>
                </div>
                <flux:badge x-cloak x-show="canScan" color="emerald" size="sm">{{ __('Active') }}</flux:badge>
                <flux:badge x-cloak x-show="!canScan" color="amber" size="sm">{{ __('Ended') }}</flux:badge>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800/50 dark:bg-amber-950/30">
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                <flux:text class="text-sm text-amber-800 dark:text-amber-300">
                    {{ __('No attendance schedule is currently active today.') }}
                </flux:text>
            </div>
        </div>
    @endif

    {{-- Camera scanner panel --}}
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-zinc-200 bg-zinc-950 dark:border-zinc-700"
    >
        {{-- Header bar --}}
        <div class="flex items-center justify-between border-b border-zinc-800 px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full"
                    :class="active ? 'animate-pulse bg-emerald-500' : 'bg-zinc-600'">
                </span>
                <flux:text class="text-sm font-medium text-zinc-200">
                    <span x-text="active ? '{{ __('Camera active') }}' : '{{ __('Camera off') }}'"></span>
                </flux:text>
            </div>

            <flux:button
                wire:click="toggleCamera"
                size="sm"
                variant="{{ $cameraActive ? 'danger' : 'primary' }}"
                icon="{{ $cameraActive ? 'video-camera-slash' : 'video-camera' }}"
                x-bind:disabled="!canScan"
            >
                {{ $cameraActive ? __('Stop camera') : __('Start camera') }}
            </flux:button>
        </div>

        {{-- Video / placeholder --}}
        <div class="relative flex flex-1 items-center justify-center bg-zinc-950">
            {{-- Camera view --}}
            <div x-cloak x-show="active" class="w-full p-4" wire:ignore>
                <div id="reader" class="overflow-hidden rounded-lg [&_video]:w-full [&_video]:rounded-lg [&_video]:object-cover"></div>
            </div>

            {{-- Idle placeholder --}}
            <div x-cloak x-show="!active" class="flex flex-col items-center gap-3 p-8 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-800">
                    <flux:icon.qr-code class="h-10 w-10 text-zinc-500" />
                </div>
                <flux:text class="text-sm text-zinc-400">
                    @if ($schedule)
                        <span x-cloak x-show="canScan">{{ __('Press "Start camera" to begin scanning QR codes.') }}</span>
                        <span x-cloak x-show="!canScan">{{ __('Scanning is unavailable outside of scheduled sessions.') }}</span>
                    @else
                        {{ __('Scanning is unavailable outside of scheduled sessions.') }}
                    @endif
                </flux:text>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
@endpush

<style>
    [x-cloak] {
        display: none !important;
    }

    #reader__scan_region {
        min-height: 20rem;
    }
</style>

<script>
    function qrScanner(scheduleEndsAt) {
        return {
            active: false,
            html5QrCode: null,
            isStarting: false,
            isStopping: false,
            isProcessingScan: false,
            stopPromise: null,
            lastCode: null,
            lastCodeTime: 0,
            cooldown: 2500,
            canScan: true,
            scheduleEndsAt,
            clockInterval: null,
            expiredPersisted: false,

            init() {
                this.$watch('$wire.cameraActive', (val) => {
                    this.active = val;
                });

                this.$watch('active', (val) => {
                    if (val) {
                        this.startCamera();
                    } else {
                        this.stopCamera();
                    }
                });

                this.active = this.$wire.cameraActive;
                this.updateScanAvailability();
                this.clockInterval = window.setInterval(() => this.updateScanAvailability(), 1000);
            },

            updateScanAvailability() {
                if (! this.scheduleEndsAt) {
                    this.canScan = false;
                    this.active = false;
                    this.$wire.cameraActive = false;

                    return;
                }

                this.canScan = Date.now() <= new Date(this.scheduleEndsAt).getTime();

                if (! this.canScan) {
                    this.active = false;
                    this.$wire.cameraActive = false;

                    if (! this.expiredPersisted) {
                        this.expiredPersisted = true;
                        this.$wire.expireCurrentScheduleIfNeeded();
                    }
                }
            },

            async handleScanSuccess(decodedText) {
                if (this.isProcessingScan || ! this.canScan) {
                    return;
                }

                const now = Date.now();

                if (decodedText === this.lastCode && now - this.lastCodeTime < this.cooldown) {
                    return;
                }

                this.isProcessingScan = true;
                this.lastCode = decodedText;
                this.lastCodeTime = now;
                this.active = false;
                this.$wire.cameraActive = false;
                await this.stopCamera();
                await this.$wire.processQrCode(decodedText);
            },

            async startCamera() {
                if (! this.canScan || this.isStarting || this.isStopping) {
                    return;
                }

                if (this.html5QrCode?.isScanning) {
                    return;
                }

                if (! window.Html5Qrcode) {
                    alert('Không thể tải thư viện quét QR. Hãy tải lại trang rồi thử lại.');
                    this.$wire.cameraActive = false;

                    return;
                }

                this.isStarting = true;
                this.isProcessingScan = false;
                this.lastCode = null;
                this.lastCodeTime = 0;
                this.expiredPersisted = false;

                try {
                    await this.$nextTick();

                    if (! this.html5QrCode) {
                        this.html5QrCode = new Html5Qrcode('reader');
                    }

                    await this.startScannerWithFallback();
                } catch (err) {
                    console.error('Camera error:', err);
                    alert('Không thể mở camera. Hãy kiểm tra quyền truy cập rồi thử lại.');
                    this.$wire.cameraActive = false;
                } finally {
                    this.isStarting = false;
                }
            },

            async startScannerWithFallback() {
                const scanConfig = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250,
                    },
                    formatsToSupport: window.Html5QrcodeSupportedFormats
                        ? [Html5QrcodeSupportedFormats.QR_CODE]
                        : undefined,
                };

                const cameraCandidates = [
                    { facingMode: 'environment' },
                    { facingMode: 'user' },
                ];

                if (typeof Html5Qrcode.getCameras === 'function') {
                    const cameras = await Html5Qrcode.getCameras();
                    const uniqueCameraIds = [...new Set(cameras.map((camera) => camera.id).filter(Boolean))];

                    if (uniqueCameraIds.length > 0) {
                        cameraCandidates.push(uniqueCameraIds.at(-1));
                    }

                    if (uniqueCameraIds.length > 1) {
                        cameraCandidates.push(uniqueCameraIds[0]);
                    }
                }

                let lastError = null;

                for (const cameraCandidate of cameraCandidates) {
                    try {
                        await this.html5QrCode.start(
                            cameraCandidate,
                            scanConfig,
                            (decodedText) => this.handleScanSuccess(decodedText)
                        );

                        return;
                    } catch (err) {
                        lastError = err;
                    }
                }

                throw lastError ?? new Error('Unable to start camera.');
            },

            async stopCamera() {
                if (! this.html5QrCode) {
                    return;
                }

                if (this.stopPromise) {
                    await this.stopPromise;

                    return;
                }

                this.isStopping = true;
                const html5QrCode = this.html5QrCode;

                this.stopPromise = (async () => {
                    try {
                        if (html5QrCode.isScanning) {
                            await html5QrCode.stop();
                        }
                    } catch (err) {
                        console.error('Failed to stop camera:', err);
                    }

                    try {
                        if (! html5QrCode.isScanning) {
                            await html5QrCode.clear();
                        }
                    } catch (err) {
                        const message = String(err?.message ?? err);

                        if (! message.includes('Cannot clear while scan is ongoing')) {
                            console.error('Failed to clear QR reader surface:', err);
                        }
                    } finally {
                        this.isProcessingScan = false;
                        this.isStopping = false;
                        this.html5QrCode = null;
                        this.stopPromise = null;
                    }
                })();

                await this.stopPromise;
            },

            destroy() {
                if (this.clockInterval) {
                    window.clearInterval(this.clockInterval);
                }

                this.stopCamera();
            }
        }
    }
</script>
