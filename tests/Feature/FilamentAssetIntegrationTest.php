<?php

it('registers filament blade views as tailwind sources', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain("@source '../../vendor/filament/**/*.blade.php';");
});

it('renders filament styles from the shared head partial', function () {
    $head = file_get_contents(resource_path('views/partials/head.blade.php'));
    $layout = file_get_contents(resource_path('views/layouts/app/sidebar.blade.php'));

    expect($head)->toContain('@filamentStyles')
        ->and($layout)->not->toContain('@filamentStyles');
});
