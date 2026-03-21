<div>
    <flux:modal wire:model="showTransactionModal" class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingTransactionId ? __('Edit transaction') : __('Create transaction') }}
                </flux:heading>
                <flux:text>{{ __('Manage common fund income, expenses, and attached documents.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-1">
                    <flux:date-picker wire:model.live="transaction_date" :label="__('Transaction date')" locale="vi-VN" />
                </div>
                <div class="md:col-span-1">
                    <flux:autocomplete wire:model="transaction_item" :label="__('Fund item')">
                        @foreach ($this->transactionItemSuggestions() as $transactionItemSuggestion)
                            <flux:autocomplete.item>{{ $transactionItemSuggestion }}</flux:autocomplete.item>
                        @endforeach
                    </flux:autocomplete>
                </div>
                <div class="md:col-span-2">
                    <flux:textarea wire:model.live.debounce.500ms="description" :label="__('Description')" rows="3" />
                </div>
                <div class="md:col-span-1">
                    <flux:select wire:model.live="type" variant="listbox" :label="__('Transaction type')">
                        <flux:select.option value="income">{{ __('Income') }}</flux:select.option>
                        <flux:select.option value="expense">{{ __('Expense') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="amount" type="number" min="1" step="1" :label="__('Amount')" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="in_charge" :label="__('In charge')" />
                </div>
                <div class="md:col-span-1">
                    <flux:select wire:model.live="status" variant="listbox" :label="__('Transaction status')">
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-2 space-y-3">
                    @if ($attachment)
                        <div class="rounded-2xl border border-(--color-background) p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $attachment->getClientOriginalName() }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Selected file ready to be saved with this transaction.') }}
                                    </div>
                                </div>
                                <flux:button size="sm" variant="ghost" color="rose" wire:click="removeSelectedAttachment">
                                    {{ __('Remove file') }}
                                </flux:button>
                            </div>
                        </div>
                    @elseif ($existingAttachment && ! $removeCurrentAttachment)
                        <div class="rounded-2xl border border-(--color-background) p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ basename($existingAttachment) }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Current file attached to this transaction.') }}
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <flux:button size="sm" variant="ghost" :href="\Illuminate\Support\Facades\Storage::url($existingAttachment)" target="_blank" icon="paper-clip">
                                        {{ __('Open') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" color="rose" wire:click="removeExistingAttachment">
                                        {{ __('Remove file') }}
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @elseif ($removeCurrentAttachment)
                        <flux:callout color="amber" icon="exclamation-circle" :heading="__('Current attachment will be removed after saving.')">
                            <div class="mt-3">
                                <flux:button size="sm" variant="ghost" wire:click="undoRemoveExistingAttachment">
                                    {{ __('Undo') }}
                                </flux:button>
                            </div>
                        </flux:callout>
                    @endif

                    @if (! $attachment)
                        <flux:file-upload wire:model.live="attachment" :label="__('Upload file')" accept=".pdf,.xls,.xlsx">
                            <flux:file-upload.dropzone
                                :heading="__('Drop a file here or click to browse')"
                                :text="__('PDF, XLS, or XLSX up to 10MB')"
                                with-progress
                                inline
                            />
                        </flux:file-upload>
                    @endif

                    <flux:error name="attachment" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeTransactionModal">{{ __('Cancel') }}</flux:button>
                @if ($this->shouldShowSaveTransactionButton())
                    <flux:button variant="primary" wire:click="saveTransaction">
                        {{ $editingTransactionId ? __('Save transaction') : __('Add transaction') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete transaction') }}</flux:heading>
                <flux:text>{{ __('This transaction will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteTransaction')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteTransaction">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
