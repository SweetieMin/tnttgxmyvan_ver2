<?php

test('theme css keeps dark mode tied to the active theme palette', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain(".dark [data-theme],\n    [data-theme].dark")
        ->toContain('--color-accent: var(--theme-600);')
        ->toContain('--color-accent-content: var(--theme-400);')
        ->toContain('--color-background: color-mix(in oklab, var(--theme-900) 60%, var(--color-zinc-950));')
        ->toContain('--color-background-icon: color-mix(in oklab, var(--theme-800) 55%, var(--color-zinc-900));')
        ->toContain('--color-text-hidden: var(--theme-200);')
        ->toContain('--color-heading-table: var(--theme-400);');
});
