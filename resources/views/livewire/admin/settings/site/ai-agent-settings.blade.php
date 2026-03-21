<section class="w-full">
    @include('partials.site-settings-heading')

    <x-layouts::settings.site.layout :heading="__('AI Agent configuration')" :subheading="__('Manage API credentials and endpoints for system AI agents.')">
        <div class="space-y-4">
            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Group') }}</flux:table.column>
                        <flux:table.column>{{ __('Value') }}</flux:table.column>
                        <flux:table.column>{{ __('Label') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->agentSettings() as $setting)
                            <flux:table.row wire:key="agent-setting-row-{{ $setting->id }}">
                                <flux:table.cell>
                                    <span class="font-medium">{{ $setting->group }}</span>
                                </flux:table.cell>

                                <flux:table.cell>
                                   
                                        <span class="inline-block max-w-56 truncate font-mono text-sm md:max-w-80">
                                            {{ $this->displaySettingValue($setting) }}
                                        </span>
                                   
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex items-center justify-between gap-3">
                                        <span>{{ $setting->label }}</span>

                                        @can('settings.site.ai-agent.update')
                                            <flux:button variant="ghost" size="sm" wire:click="editSetting({{ $setting->id }})">
                                                {{ __('Edit') }}
                                            </flux:button>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="3" class="py-8 text-center text-zinc-500">
                                    {{ __('No AI agent settings found.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        <flux:modal name="edit-agent-setting" class="md:w-xl">
            <form wire:submit.prevent="updateAgentSetting()" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Edit AI agent setting') }}</flux:heading>
                    <flux:subheading>
                        {{ $editingGroup !== '' ? $editingGroup : __('AI agent') }}
                    </flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-1">
                        <flux:input :label="__('Group')" :value="$editingGroup" readonly />
                    </div>

                    <div class="md:col-span-1">
                        <flux:input :label="__('Label')" :value="$editingLabel" readonly />
                    </div>
                </div>

                @if ($editingIsEncrypted)
                    <flux:textarea wire:model="editingValue" :label="__('Value')" type="password" viewable  class="min-h-35"/>
                @else
                    <flux:textarea wire:model="editingValue" :label="__('Value')" type="text" class="min-h-35"/>
                @endif

                <div class="flex items-center justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    @can('settings.site.ai-agent.update')
                        @if ($this->hasEditingChanges())
                            <flux:button type="submit" variant="primary">
                                {{ __('Save') }}
                            </flux:button>
                        @endif
                    @endcan
                </div>
            </form>
        </flux:modal>
    </x-layouts::settings.site.layout>
</section>
