<?php

test('academic year action view does not force flux date pickers into input mode', function () {
    $view = file_get_contents(resource_path('views/livewire/admin/management/academic-year/academic-year-actions.blade.php'));

    expect($view)
        ->not->toContain('type="input"')
        ->toContain('<flux:date-picker wire:model.live="catechism_start_date"')
        ->toContain('<flux:date-picker wire:model.live="activity_end_date"');
});

test('category action view wraps grid fields with explicit col spans', function () {
    $view = file_get_contents(resource_path('views/livewire/admin/finance/categories/category-actions.blade.php'));

    expect(substr_count($view, 'class="col-span-1"'))->toBe(3);
});
