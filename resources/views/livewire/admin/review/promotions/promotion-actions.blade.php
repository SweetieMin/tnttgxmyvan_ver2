<div>
    <flux:modal wire:model="showApprovalModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Approve promotion') }}</flux:heading>
                <flux:text>
                    {{ __('Record the final promotion decision for :name and save any note for the review history.', ['name' => $approvalTargetName ?? __('the selected child')]) }}
                </flux:text>
            </div>

            <flux:textarea
                wire:model.live.debounce.300ms="approvalNote"
                :label="__('Review note')"
                :placeholder="__('Optional note for the promotion decision')"
                class="min-h-28"
            />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeApprovalModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="approvePromotion">
                    {{ __('Confirm promotion') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
