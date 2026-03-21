<section class="w-full">
    @include('partials.site-settings-heading')

    <x-layouts::settings.site.layout :heading="__('Mail configuration')" :subheading="__('Update the email system settings')">

        <form wire:submit.prevent="updateEmailSettings()" class="my-6 w-full">
            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-8 items-start">

                <div class="space-y-6">
                    <flux:separator :text="__('Sender information')" class="my-6" />

                    <flux:input wire:model="from_address" :label="__('From address')" type="email" autofocus
                        placeholder="noreply@example.com" wire:dirty.class="border-yellow-500!" x-data x-init="$nextTick(() => $el.focus())" />

                    <flux:input wire:model="from_name" :label="__('From name')" type="text"
                        :placeholder="__('Parish Youth Group')" wire:dirty.class="border-yellow-500!" />

                    <flux:input wire:model="reply_to_address" :label="__('Reply-to address')" type="email"
                        placeholder="reply@example.com" wire:dirty.class="border-yellow-500!" />

                    <flux:input wire:model="username" :label="__('SMTP username')" type="text"
                        placeholder="mailer@example.com" wire:dirty.class="border-yellow-500!" />

                    <div class="space-y-2">
                        @if ($isHavePassword && !$showPasswordInput)
                            <flux:callout icon="shield-check" color="amber">
                                <flux:callout.heading>{{ __('Change password') }}</flux:callout.heading>

                                <flux:callout.text>
                                    {{ __('For security reasons, only change the SMTP password when it is really necessary.') }}
                                    {{ __('If you want to update it, click') }}
                                    <flux:callout.link wire:click="togglePasswordInput">
                                        {{ __('Change password') }}
                                    </flux:callout.link>.
                                </flux:callout.text>
                            </flux:callout>
                        @else
                            <div class="{{ $isHavePassword ? 'flex' : '' }} gap-2 items-start">
                                <div class="flex-1">
                                    <flux:input wire:model="password" :label="__('SMTP password')" type="password"
                                        :placeholder="__('Enter a new password')" viewable
                                        wire:dirty.class="border-yellow-500!" />
                                </div>

                                @if ($isHavePassword)
                                    <flux:button variant="danger" wire:click="togglePasswordInput"
                                        class="mt-6 cursor-pointer">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                @endif

                            </div>
                        @endif
                    </div>
                </div>

                <flux:separator vertical class="hidden md:block" />

                <div class="space-y-6">
                    <flux:separator :text="__('SMTP server settings')" class="my-6" />

                    <flux:select variant="listbox" wire:model="mailer" :label="__('Mail transport')" placeholder="smtp">
                        <flux:select.option value="smtp">SMTP</flux:select.option>
                        <flux:select.option value="sendmail">Sendmail</flux:select.option>
                        <flux:select.option value="mailgun">Mailgun</flux:select.option>
                        <flux:select.option value="postmark">Postmark</flux:select.option>
                        <flux:select.option value="log">{{ __('Write to log (debug)') }}</flux:select.option>
                        <flux:select.option value="array">{{ __('Array (do not send mail)') }}</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="host" :label="__('SMTP host')" type="text"
                        placeholder="smtp.gmail.com" />

                    <flux:select wire:model.lazy="encryption" variant="listbox"
                        :label="__('Connection encryption')"
                        :placeholder="__('Connection encryption')">
                        <flux:select.option value="tls">tls</flux:select.option>
                        <flux:select.option value="ssl">ssl</flux:select.option>
                    </flux:select>

                    <flux:input wire:model="port" :label="__('SMTP port')" type="number" placeholder="587" />
                </div>

            </div>

            <flux:separator class="my-6" />

            {{-- Nút lưu --}}
            <div class="mt-8 flex items-center gap-4">
                @can('settings.site.email.update')
                    <flux:button variant="primary" type="submit" class="cursor-pointer">
                        {{ __('Save') }}
                    </flux:button>
                @endcan
            </div>
        </form>

    </x-layouts::settings.layout>

</section>
