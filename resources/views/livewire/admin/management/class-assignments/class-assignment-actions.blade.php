<div>
    <flux:modal wire:model="showCatechistAssignmentModal" class="max-w-4xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Assign catechists') }}</flux:heading>
                <flux:text>{{ __('Choose the primary catechist and supporting catechists for :class.', ['class' => $editingAcademicCourseLabel]) }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:input :label="__('Catechism class')" :value="$editingAcademicCourseLabel" readonly />
                </div>

                <div class="md:col-span-1">
                    <flux:select
                        wire:model.live="primaryCatechistId"
                        variant="listbox"
                        :label="__('Primary catechist')"
                        :placeholder="__('Select...')"
                        searchable
                    >
                        @foreach ($catechistOptions as $userId => $label)
                            <flux:select.option value="{{ $userId }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-1">
                    <flux:field>
                        <flux:label>{{ __('Supporting catechists') }}</flux:label>
                        <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/50">
                            @forelse ($catechistOptions as $userId => $label)
                                <label class="flex items-start gap-3 rounded-lg px-1 py-2">
                                    <flux:checkbox wire:model.live="assistantCatechistIds" value="{{ $userId }}" />
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                </label>
                            @empty
                                <flux:text>{{ __('No catechists available.') }}</flux:text>
                            @endforelse
                        </div>
                        <flux:error name="assistantCatechistIds" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeCatechistAssignmentModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveCatechistAssignments">{{ __('Save assignments') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
