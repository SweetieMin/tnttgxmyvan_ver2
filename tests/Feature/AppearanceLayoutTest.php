<?php

use App\Models\User;

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

test('app layout relies on flux appearance without a hard-coded dark html class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('class="dark"', false)
        ->assertSee("window.localStorage.getItem('flux.appearance.initialized')", false)
        ->assertSee("window.localStorage.setItem('flux.appearance', 'dark')", false)
        ->assertSee("window.localStorage.setItem('flux.appearance.initialized', '1')", false)
        ->assertSee("\$flux.appearance = \$flux.dark ? 'light' : 'dark'", false)
        ->assertSee(':data-theme="themePreset"', false)
        ->assertSee(':data-neutral-palette="themeNeutralPalette"', false);
});

test('auth layout relies on flux appearance without a hard-coded dark html class', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('class="dark"', false)
        ->assertSee("\$flux.appearance = \$flux.dark ? 'light' : 'dark'", false);
});
