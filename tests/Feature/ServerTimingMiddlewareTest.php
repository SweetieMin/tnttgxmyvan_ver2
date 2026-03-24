<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('server timing header is added when enabled', function () {
    config()->set('app.server_timing', true);

    Route::middleware('web')->get('/server-timing-test', function (): array {
        return [
            'users' => User::query()->count(),
        ];
    });

    $response = $this->get('/server-timing-test');

    $response->assertOk();
    expect($response->headers->get('Server-Timing'))
        ->toContain('total;dur=')
        ->toContain('db;dur=')
        ->toContain('app;dur=')
        ->toContain('queries;desc=');
});

test('server timing header is omitted when disabled', function () {
    config()->set('app.server_timing', false);

    Route::middleware('web')->get('/server-timing-disabled-test', function (): array {
        return [
            'users' => User::query()->count(),
        ];
    });

    $response = $this->get('/server-timing-disabled-test');

    $response->assertOk();
    expect($response->headers->get('Server-Timing'))->toBeNull();
});
