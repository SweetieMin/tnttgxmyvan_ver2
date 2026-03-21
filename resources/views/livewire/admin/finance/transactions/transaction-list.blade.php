<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$transactions">
        <flux:table.columns>
            <flux:table.column>{{ __('Transaction date') }}</flux:table.column>
            <flux:table.column>{{ __('Fund item') }}</flux:table.column>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Amount') }}</flux:table.column>
            <flux:table.column>{{ __('In charge') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Attachment') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($transactions as $transaction)
                <flux:table.row :key="$transaction->id" wire:key="transaction-{{ $transaction->id }}">
                    <flux:table.cell>{{ $transaction->formatted_transaction_date }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="space-y-1">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $transaction->transaction_item }}</div>
                            @if ($transaction->description)
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ Str::words($transaction->description, 20, '...') }}
                                </div>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$transaction->type_color">
                            {{ __($transaction->type_label) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end" variant="strong">{{ $transaction->formatted_amount }}</flux:table.cell>
                    <flux:table.cell>{{ $transaction->in_charge ?: '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$transaction->status_color">
                            {{ __($transaction->status_label) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($transaction->file_name)
                            <flux:button size="sm" variant="ghost" :href="\Illuminate\Support\Facades\Storage::url($transaction->file_name)" target="_blank" icon="paper-clip">
                                {{ __('Open file') }}
                            </flux:button>
                        @else
                            <span class="text-sm text-zinc-400">{{ __('No file') }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @can('finance.transaction.update')
                                <flux:button size="sm" variant="ghost"
                                    wire:click="$dispatch('edit-transaction', { transactionId: {{ $transaction->id }} })">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan
                            @can('finance.transaction.delete')
                                <flux:button size="sm" variant="danger"
                                    wire:click="$dispatch('confirm-delete-transaction', { transactionId: {{ $transaction->id }} })">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="py-8 text-center">
                        {{ __('No transactions found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
