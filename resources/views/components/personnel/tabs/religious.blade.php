@props([
    'mode' => 'show',
    'user' => null,
    'bindings' => [],
])

@php
    $blocks = [
        'baptism' => ['title' => __('Baptism'), 'date' => 'baptismDate', 'place' => 'baptismPlace', 'person' => 'baptismalSponsor', 'show_date' => 'religious_profile.baptism_date', 'show_place' => 'religious_profile.baptism_place', 'show_person' => 'religious_profile.baptismal_sponsor', 'person_label' => __('Sponsor')],
        'communion' => ['title' => __('First communion'), 'date' => 'firstCommunionDate', 'place' => 'firstCommunionPlace', 'person' => 'firstCommunionSponsor', 'show_date' => 'religious_profile.first_communion_date', 'show_place' => 'religious_profile.first_communion_place', 'show_person' => 'religious_profile.first_communion_sponsor', 'person_label' => __('Sponsor')],
        'confirmation' => ['title' => __('Confirmation'), 'date' => 'confirmationDate', 'place' => 'confirmationPlace', 'person' => 'confirmationBishop', 'show_date' => 'religious_profile.confirmation_date', 'show_place' => 'religious_profile.confirmation_place', 'show_person' => 'religious_profile.confirmation_bishop', 'person_label' => __('Bishop')],
        'pledge' => ['title' => __('Pledge'), 'date' => 'pledgeDate', 'place' => 'pledgePlace', 'person' => 'pledgeSponsor', 'show_date' => 'religious_profile.pledge_date', 'show_place' => 'religious_profile.pledge_place', 'show_person' => 'religious_profile.pledge_sponsor', 'person_label' => __('Sponsor')],
    ];
@endphp

@if ($mode === 'form')
    <div class="grid gap-4 px-6 pb-6 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($blocks as $block)
            <flux:card class="rounded-2xl p-5">
                <flux:heading size="sm">{{ $block['title'] }}</flux:heading>
                <div class="mt-4 space-y-4">
                    <flux:date-picker wire:model.live="{{ $bindings[$block['date']] }}" :label="__('Date')" type="input" locale="vi-VN" selectable-header />
                    <flux:input wire:model.live.debounce.300ms="{{ $bindings[$block['place']] }}" :label="__('Place')" />
                    <flux:input wire:model.live.debounce.300ms="{{ $bindings[$block['person']] }}" :label="$block['person_label']" />
                </div>
            </flux:card>
        @endforeach
    </div>
@else
    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($blocks as $block)
            <flux:card class="rounded-2xl p-5">
                <flux:heading size="sm">{{ $block['title'] }}</flux:heading>
                <div class="mt-4 space-y-3 text-sm">
                    <div>{{ data_get($user, $block['show_date'])?->format('d/m/Y') ?? '—' }}</div>
                    <div>{{ data_get($user, $block['show_place']) ?: '—' }}</div>
                    <div>{{ data_get($user, $block['show_person']) ?: '—' }}</div>
                </div>
            </flux:card>
        @endforeach
    </div>
@endif
