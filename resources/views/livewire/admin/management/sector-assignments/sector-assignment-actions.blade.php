<div>
    <flux:modal wire:model="showLeaderAssignmentModal" class="max-w-5xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Assign leaders') }}</flux:heading>
                <flux:text>{{ __('Choose the sector head, vice head, and leaders for :sector.', ['sector' => $editingSectorName]) }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:input :label="__('Sector')" :value="$editingSectorName" readonly />
                </div>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>{{ __('Related catechism classes') }}</flux:label>
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                            <flux:text>{{ $editingSectorCourseNames !== [] ? implode(', ', $editingSectorCourseNames) : __('No catechism classes found for this sector.') }}</flux:text>
                        </div>
                    </flux:field>
                </div>

                <div class="md:col-span-1">
                    <flux:select
                        wire:model.live="sectorHeadUserId"
                        variant="listbox"
                        :label="__('Sector head')"
                        :placeholder="__('Select...')"
                        searchable
                    >
                        @foreach ($leaderOptions as $userId => $label)
                            <flux:select.option value="{{ $userId }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-1">
                    <flux:select
                        wire:model.live="viceSectorHeadUserId"
                        variant="listbox"
                        :label="__('Vice sector head')"
                        :placeholder="__('Select...')"
                        searchable
                    >
                        @foreach ($leaderOptions as $userId => $label)
                            <flux:select.option value="{{ $userId }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:field>
                        <flux:label>{{ __('Sector leaders') }}</flux:label>
                        <div class="max-h-80 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                            @forelse ($leaderOptions as $userId => $label)
                                <label class="flex items-start gap-3 rounded-lg px-1 py-2">
                                    <flux:checkbox wire:model.live="leaderIds" value="{{ $userId }}" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                </label>
                            @empty
                                <flux:text>{{ __('No leaders available.') }}</flux:text>
                            @endforelse
                        </div>
                        <flux:error name="leaderIds" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeLeaderAssignmentModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveLeaderAssignments">{{ __('Save assignments') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
