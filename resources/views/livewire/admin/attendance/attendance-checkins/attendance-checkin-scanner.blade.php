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
                    {{ __('No attendance schedule is active right now.') }}
                </flux:text>
            </div>
        </div>
    @endif

    {{-- Camera scanner panel --}}
    <div
        class="flex flex-1 flex-col overflow-hidden rounded-xl border border-zinc-200 bg-zinc-950 dark:border-zinc-700"
        x-data="qrScanner(@entangle('cameraActive').live)"
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
            <div x-show="active" class="relative w-full">
                <video
                    x-ref="video"
                    class="w-full"
                    autoplay
                    muted
                    playsinline
                ></video>
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Scan overlay --}}
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="relative h-52 w-52">
                        <div class="absolute inset-0 rounded-2xl border-2 border-white/20"></div>
                        <div class="absolute left-0 top-0 h-8 w-8 rounded-tl-2xl border-l-3 border-t-3 border-emerald-400"></div>
                        <div class="absolute right-0 top-0 h-8 w-8 rounded-tr-2xl border-r-3 border-t-3 border-emerald-400"></div>
                        <div class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-2xl border-b-3 border-l-3 border-emerald-400"></div>
                        <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-2xl border-b-3 border-r-3 border-emerald-400"></div>
                        <div class="scan-line absolute inset-x-2 h-0.5 bg-emerald-400/80 shadow-[0_0_8px_2px_rgba(52,211,153,0.6)]"></div>
                    </div>
                </div>
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

        {{-- Scan result feedback --}}
        @if ($lastScanStatus)
            <div class="border-t border-zinc-800 px-4 py-3">
                @if ($lastScanStatus === 'success')
                    <div class="flex items-center gap-3 rounded-lg bg-emerald-900/40 px-3 py-2">
                        <flux:icon.check-circle class="h-5 w-5 shrink-0 text-emerald-400" />
                        <div>
                            <flux:text class="text-sm font-semibold text-emerald-300">{{ __('Checked in!') }}</flux:text>
                            <flux:text class="text-xs text-emerald-400">{{ $lastScannedUser['name'] ?? '' }}</flux:text>
                        </div>
                    </div>
                @elseif ($lastScanStatus === 'already')
                    <div class="flex items-center gap-3 rounded-lg bg-amber-900/40 px-3 py-2">
                        <flux:icon.exclamation-triangle class="h-5 w-5 shrink-0 text-amber-400" />
                        <div>
                            <flux:text class="text-sm font-semibold text-amber-300">{{ __('Already checked in') }}</flux:text>
                            <flux:text class="text-xs text-amber-400">{{ $lastScannedUser['name'] ?? '' }}</flux:text>
                        </div>
                    </div>
                @elseif ($lastScanStatus === 'not_enrolled')
                    <div class="flex items-center gap-3 rounded-lg bg-rose-900/40 px-3 py-2">
                        <flux:icon.x-circle class="h-5 w-5 shrink-0 text-rose-400" />
                        <div>
                            <flux:text class="text-sm font-semibold text-rose-300">{{ __('Not enrolled') }}</flux:text>
                            <flux:text class="text-xs text-rose-400">{{ $lastScannedUser['name'] ?? '' }}</flux:text>
                        </div>
                    </div>
                @elseif ($lastScanStatus === 'not_found')
                    <div class="flex items-center gap-3 rounded-lg bg-rose-900/40 px-3 py-2">
                        <flux:icon.x-circle class="h-5 w-5 shrink-0 text-rose-400" />
                        <flux:text class="text-sm font-semibold text-rose-300">{{ __('QR code not recognized') }}</flux:text>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
@endpush

<style>
    @keyframes scanMove {
        0%, 100% { top: 8px; }
        50% { top: calc(100% - 10px); }
    }
    .scan-line { animation: scanMove 2s ease-in-out infinite; }
</style>

<script>
    function qrScanner(cameraActiveEntangle) {
        return {
            active: false,
            stream: null,
            scanInterval: null,
            lastCode: null,
            lastCodeTime: 0,
            cooldown: 2000,

            init() {
                this.$watch('active', (val) => {
                    if (val) {
                        this.startCamera();
                    } else {
                        this.stopCamera();
                    }
                });

                this.$watch(() => cameraActiveEntangle, (val) => {
                    this.active = val;
                });
            },

            async startCamera() {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
                    });
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play();
                    this.scanInterval = setInterval(() => this.scanFrame(), 250);
                } catch (err) {
                    console.error('Camera error:', err);
                    this.active = false;
                    cameraActiveEntangle = false;
                }
            },

            stopCamera() {
                clearInterval(this.scanInterval);
                this.scanInterval = null;
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
            },

            scanFrame() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                if (!video || video.readyState !== video.HAVE_ENOUGH_DATA) return;

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    const now = Date.now();
                    if (code.data !== this.lastCode || now - this.lastCodeTime > this.cooldown) {
                        this.lastCode = code.data;
                        this.lastCodeTime = now;
                        this.$wire.processQrCode(code.data);
                    }
                }
            },

            destroy() {
                this.stopCamera();
            }
        }
    }
</script>
