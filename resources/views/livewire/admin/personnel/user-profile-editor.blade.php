<section class="flex flex-col gap-4">
    <x-app-heading :title="$this->title()" :sub-title="$this->subtitle()" icon="user-circle" />

    <form wire:submit="saveUserProfile" class="space-y-4 pb-28 md:pb-0">
        <div class="grid gap-4 xl:grid-cols-[450px_minmax(0,1fr)]">
            <div class="space-y-4 xl:sticky xl:top-6 xl:self-start">
                <flux:card class="rounded-3xl p-8">

                    @if ($this->accountCode() !== '')
                        <div class="space-y-2 text-center mb-6">
                                <flux:heading size="xl">
                                    {{ $this->accountCode() }}
                                </flux:heading>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div class="flex flex-col items-center gap-4 text-center">
                            <x-media.avatar-crop-upload
                                :enabled="$this->canUploadAvatar()"
                                :preview-url="$this->avatarPreviewUrl()"
                                file-model="pictureUpload"
                                modal-model="showAvatarCropModal"
                                preview-model="cropPreviewUrl"
                                output-model="croppedImageData"
                            />

                            <flux:error name="pictureUpload" />

                            <div class="space-y-1">

                                <flux:heading size="xl">
                                    {{ filled($christianName) !== '' ? trim($christianName) : $this->groupLabel() }}
                                </flux:heading>

                                <flux:heading size="xl">
                                    {{ $fullName !== '' ? $fullName : __('New profile') }}
                                </flux:heading>

                            </div>
                        </div>


                        @if ($this->profileQrCodeSvg() !== null)
                            <flux:card class="flex items-center justify-center">
                                {!! $this->profileQrCodeSvg() !!}

                            </flux:card>
                        @endif
                    </div>
                </flux:card>
            </div>

            <flux:card class="overflow-hidden rounded-3xl p-0">
                <flux:tab.group>
                    <flux:tabs scrollable scrollable:fade
                        class="border-b border-zinc-200 px-6 pt-4 dark:border-zinc-800">
                        <flux:tab wire:click="selectTab('personal')" name="personal" icon="user"
                            :selected="$tab === 'personal'">{{ __('Personal information') }}</flux:tab>
                        <flux:tab wire:click="selectTab('parents')" name="parents" icon="users"
                            :selected="$tab === 'parents'">{{ __('Parents') }}</flux:tab>
                        <flux:tab wire:click="selectTab('religious')" name="religious" icon="sparkles"
                            :selected="$tab === 'religious'">{{ __('Religious profile') }}</flux:tab>
                        <flux:tab wire:click="selectTab('academic')" name="academic" icon="academic-cap"
                            :selected="$tab === 'academic'">{{ __('Study / class') }}</flux:tab>
                        <flux:tab wire:click="selectTab('settings')" name="settings" icon="cog-6-tooth"
                            :selected="$tab === 'settings'">{{ __('Settings') }}</flux:tab>
                    </flux:tabs>

                    <flux:tab.panel name="personal">
                        <x-personnel.tabs.personal mode="form" :role-options="$this->roleOptions()" :bindings="[
                            'selectedRoleNames' => 'selectedRoleNames',
                            'statusLogin' => 'statusLogin',
                            'christianName' => 'christianName',
                            'fullName' => 'fullName',
                            'birthday' => 'birthday',
                            'address' => 'address',
                            'phone' => 'phone',
                            'email' => 'email',
                            'gender' => 'gender',
                            'bio' => 'bio',
                        ]" />
                    </flux:tab.panel>

                    <flux:tab.panel name="parents">
                        <x-personnel.tabs.parents mode="form" :bindings="[
                            'fatherChristianName' => 'fatherChristianName',
                            'fatherName' => 'fatherName',
                            'fatherPhone' => 'fatherPhone',
                            'motherChristianName' => 'motherChristianName',
                            'motherName' => 'motherName',
                            'motherPhone' => 'motherPhone',
                            'godParentChristianName' => 'godParentChristianName',
                            'godParentName' => 'godParentName',
                            'godParentPhone' => 'godParentPhone',
                        ]" />
                    </flux:tab.panel>

                    <flux:tab.panel name="religious">
                        <x-personnel.tabs.religious mode="form" :bindings="[
                            'baptismDate' => 'baptismDate',
                            'baptismPlace' => 'baptismPlace',
                            'baptismalSponsor' => 'baptismalSponsor',
                            'firstCommunionDate' => 'firstCommunionDate',
                            'firstCommunionPlace' => 'firstCommunionPlace',
                            'firstCommunionSponsor' => 'firstCommunionSponsor',
                            'confirmationDate' => 'confirmationDate',
                            'confirmationPlace' => 'confirmationPlace',
                            'confirmationBishop' => 'confirmationBishop',
                            'pledgeDate' => 'pledgeDate',
                            'pledgePlace' => 'pledgePlace',
                            'pledgeSponsor' => 'pledgeSponsor',
                        ]" />
                    </flux:tab.panel>

                    <flux:tab.panel name="academic">
                        <x-personnel.tabs.academic mode="form" :bindings="[
                            'statusReligious' => 'statusReligious',
                            'isAttendance' => 'isAttendance',
                        ]" :class-placement-text="__(
                            'Class and academic-year placement will be connected after the enrollment module is added.',
                        )" />
                    </flux:tab.panel>

                    <flux:tab.panel name="settings">
                        <x-personnel.tabs.settings mode="form" :bindings="[
                            'lang' => 'lang',
                        ]" />
                    </flux:tab.panel>
                </flux:tab.group>
            </flux:card>
        </div>

        <div class="hidden justify-end gap-3 md:flex">
            <flux:button variant="ghost" :href="$this->cancelRoute()" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button variant="primary" type="submit">
                {{ $this->submitLabel() }}
            </flux:button>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-0 border-t border-zinc-200 bg-white/95 px-4 pt-3 pb-[calc(env(safe-area-inset-bottom)+0.75rem)] backdrop-blur md:hidden dark:border-zinc-800 dark:bg-zinc-950/95">
            <div class="mx-auto grid max-w-screen-sm grid-cols-2 gap-3">
                <flux:button variant="ghost" :href="$this->cancelRoute()" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ $this->submitLabel() }}
                </flux:button>
            </div>
        </div>
    </form>
</section>
