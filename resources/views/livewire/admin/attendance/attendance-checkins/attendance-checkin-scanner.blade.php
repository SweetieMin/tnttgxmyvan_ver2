<div class="flex h-full flex-col gap-4">
    @php $schedule = $this->currentSchedule(); @endphp

    {{-- Schedule info banner --}}
    @if ($schedule)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800/50 dark:bg-emerald-950/30">
            <div class="flex items-start gap-3">
                <flux:icon.calendar-days class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
                <div class="min-w-0 flex-1">
                    <flux:text class="font-semibold text-emerald-800 dark:text-emerald-300">
                        {{ $schedule->title }}
                    </flux:text>
                    <flux:text class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-500">
                        {{ $schedule->attendance_date?->format('d/m/Y') }}
                        &middot;
                        {{ substr((string) $schedule->start_time, 0, 5) }} – {{ substr((string) $schedule->end_time, 0, 5) }}
                    </flux:text>
                </div>
                <flux:badge color="emerald" size="sm">{{ __('Active') }}</flux:badge>
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
        x-data="qrScanner()"
        x-init="init()"
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
                
            >
                {{ $cameraActive ? __('Stop camera') : __('Start camera') }}
            </flux:button>
        </div>

        {{-- Video / placeholder --}}
        <div class="relative flex flex-1 items-center justify-center bg-zinc-950">
            {{-- Camera view --}}
            <div x-show="active" class="w-full p-4" wire:ignore>
                <div id="reader" class="overflow-hidden rounded-lg [&_video]:w-full [&_video]:rounded-lg [&_video]:object-cover"></div>
            </div>

            {{-- Idle placeholder --}}
            <div x-show="!active" class="flex flex-col items-center gap-3 p-8 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-800">
                    <flux:icon.qr-code class="h-10 w-10 text-zinc-500" />
                </div>
                <flux:text class="text-sm text-zinc-400">
                    @if ($schedule)
                        {{ __('Press "Start camera" to begin scanning QR codes.') }}
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

<script>
    function qrScanner() {
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
            },

            async handleScanSuccess(decodedText) {
                if (this.isProcessingScan || this.isStopping) {
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
                if (this.isStarting || this.isStopping) {
                    return;
                }

                if (this.html5QrCode?.isScanning) {
                    return;
                }

                this.isStarting = true;
                this.isProcessingScan = false;
                this.lastCode = null;
                this.lastCodeTime = 0;

                try {
                    if (! this.html5QrCode) {
                        this.html5QrCode = new Html5Qrcode('reader');
                    }

                    await this.html5QrCode.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250,
                            },
                        },
                        (decodedText) => this.handleScanSuccess(decodedText)
                    );
                } catch (err) {
                    console.error('Camera error:', err);
                    alert('Lỗi kết nối camera: Không thể mở camera. Hãy kiểm tra lại quyền truy cập hoặc đảm bảo bạn dùng màn hình HTTPS.');
                    this.$wire.cameraActive = false;
                } finally {
                    this.isStarting = false;
                }
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

                this.stopPromise = (async () => {
                    try {
                        if (this.html5QrCode.isScanning) {
                            await this.html5QrCode.stop();
                        }
                    } catch (err) {
                        console.error('Failed to stop camera:', err);
                    }

                    try {
                        if (! this.html5QrCode.isScanning) {
                            await this.html5QrCode.clear();
                        }
                    } catch (err) {
                        const message = String(err?.message ?? err);

                        if (! message.includes('Cannot clear while scan is ongoing')) {
                            console.error('Failed to clear QR reader surface:', err);
                        }
                    } finally {
                        this.isProcessingScan = false;
                        this.isStopping = false;
                        this.stopPromise = null;
                    }
                })();

                await this.stopPromise;
            },

            destroy() {
                this.stopCamera();
            }
        }
    }
</script>
