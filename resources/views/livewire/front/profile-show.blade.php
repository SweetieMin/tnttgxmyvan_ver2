<section class="space-y-6">
    <flux:card class="overflow-hidden rounded-4xl border border-amber-200/80 bg-white/90 p-0 shadow-sm shadow-amber-100 backdrop-blur">
        <div class="bg-gradient-to-r from-amber-100 via-amber-50 to-white px-6 py-8 md:px-10">
            <div class="flex flex-col items-center gap-6 text-center md:flex-row md:items-center md:text-left">
                <img src="{{ $this->avatarUrl() }}" alt="{{ $user->full_name }}"
                    class="size-32 rounded-full border-4 border-white object-cover shadow-sm shadow-amber-200 md:size-36" />

                <div class="min-w-0 flex-1 space-y-3">
                    <div class="space-y-1">

                        @if (filled($user->christian_name))
                            <p class="text-base font-medium text-amber-800">
                                {{ $user->christian_name }}
                            </p>
                        @endif

                        <h1 class="text-3xl font-bold tracking-tight text-zinc-900">
                            {{ $user->full_name }}
                        </h1>

                    </div>

                    @if ($this->roleNames()->isNotEmpty())
                        <div class="flex flex-wrap justify-center gap-2 md:justify-start">
                            @foreach ($this->roleNames() as $roleName)
                                <flux:badge color="amber">{{ $roleName }}</flux:badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-4 py-4 md:px-8 md:py-6">
            <flux:card >
                <flux:tab.group>
                <flux:tabs scrollable scrollable:fade class="border-b border-zinc-200 pb-2 dark:border-zinc-800">
                    <flux:tab wire:click="selectTab('personal')" name="personal" icon="user"
                        :selected="$tab === 'personal'">
                        {{ __('Personal information') }}
                    </flux:tab>
                    <flux:tab wire:click="selectTab('parents')" name="parents" icon="users"
                        :selected="$tab === 'parents'">
                        {{ __('Parents information') }}
                    </flux:tab>
                </flux:tabs>

                <flux:tab.panel name="personal">
                    <div class="grid gap-2  md:grid-cols-2">
                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Christian name') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">{{ $user->christian_name ?: '—' }}
                            </div>
                        </flux:card>

                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Full name') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">{{ $user->full_name }}</div>
                        </flux:card>

                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Birthday') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">
                                {{ $user->birthday?->format('d/m/Y') ?: '—' }}
                            </div>
                        </flux:card>

                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Gender') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">{{ $this->genderLabel() }}</div>
                        </flux:card>

                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Phone') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">
                                {{ $this->maskedPhone(data_get($user, 'details.phone')) }}
                            </div>
                        </flux:card>

                        <flux:card class="rounded-2xl p-5">
                            <div class="text-sm text-zinc-500">{{ __('Address') }}</div>
                            <div class="mt-2 text-lg font-semibold text-zinc-900">
                                {{ data_get($user, 'details.address') ?: '—' }}
                            </div>
                        </flux:card>
                    </div>
                </flux:tab.panel>

                <flux:tab.panel name="parents">
                    <div class="grid gap-2  md:grid-cols-2">
                        @forelse ($this->parentContacts() as $contact)
                            <flux:card class="rounded-2xl p-5">
                                <div class="space-y-3">
                                    <div class="text-sm font-medium uppercase tracking-[0.2em] text-amber-700">
                                        {{ $contact['label'] }}
                                    </div>
                                    <div>
                                        <div class="text-sm text-zinc-500">{{ __('Name') }}</div>
                                        <div class="mt-1 text-lg font-semibold text-zinc-900">
                                            {{ $contact['name'] !== '' ? $contact['name'] : '—' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-zinc-500">{{ __('Phone') }}</div>
                                        <div class="mt-1 text-lg font-semibold text-zinc-900">
                                            {{ $contact['phone'] }}
                                        </div>
                                    </div>
                                </div>
                            </flux:card>
                        @empty
                            <flux:card class="rounded-2xl p-6 md:col-span-2">
                                <div class="text-center text-zinc-500">
                                    {{ __('No parent information available.') }}
                                </div>
                            </flux:card>
                        @endforelse
                    </div>
                </flux:tab.panel>
                </flux:tab.group>
            </flux:card>
        </div>
    </flux:card>
</section>
