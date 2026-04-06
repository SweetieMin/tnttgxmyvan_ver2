<?php

test('filament theme variables follow the shared app theme contract', function () {
    $css = file_get_contents(__DIR__.'/../../../resources/css/filament.css');
    $appCss = file_get_contents(__DIR__.'/../../../resources/css/app.css');

    expect($css)->not->toBeFalse()
        ->and($appCss)->not->toBeFalse()
        ->and($css)->toContain('--primary-500: var(--theme-500);')
        ->and($css)->toContain('--primary-950: var(--theme-950);')
        ->and($css)->toContain('body[data-theme]')
        ->and($appCss)->toContain('[data-theme=\'sky\']')
        ->and($appCss)->toContain('--theme-500: var(--color-sky-500);')
        ->and($appCss)->toContain('--theme-950: var(--color-sky-950);')
        ->and($css)->toContain('body[data-neutral-palette]')
        ->and($css)->toContain('--gray-500: var(--color-zinc-500);')
        ->and($css)->toContain('.fi-btn.fi-color-primary:not(.fi-outlined)')
        ->and($css)->toContain('--bg: var(--color-accent);')
        ->and($css)->toContain('--text: var(--color-accent-foreground);')
        ->and($css)->toContain(".fi-modal-footer .fi-ac-btn-action[type='submit']")
        ->and($css)->toContain(".fi-modal-footer .fi-ac-btn-action[type='submit'].fi-color-info")
        ->and($css)->toContain('--color-600: var(--theme-600);');
});
