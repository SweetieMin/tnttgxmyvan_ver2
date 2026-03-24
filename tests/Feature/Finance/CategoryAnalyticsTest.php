<?php

use App\Livewire\Admin\Finance\Categories\CategoryAnalytics;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Permission::findOrCreate('finance.category.view', 'web');
    Cache::flush();
});

test('authorized users can visit the category analytics page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.category.view');

    $response = $this->actingAs($user)->get(route('admin.finance.categories.analytics'));

    $response->assertOk()
        ->assertSeeText(__('Category analytics'))
        ->assertSeeText(__('Detailed category breakdown'));
});

test('category analytics applies the selected filters to overview data', function () {
    Carbon::setTestNow('2026-03-24 10:00:00');

    $user = User::factory()->create();
    $user->givePermissionTo('finance.category.view');

    $trungThu = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trung Thu',
    ]);

    $tet = Category::factory()->create([
        'ordering' => 2,
        'name' => 'Tet',
    ]);

    Transaction::factory()->create([
        'category_id' => $trungThu->id,
        'transaction_date' => '2026-03-10',
        'type' => 'income',
        'amount' => 1000000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $trungThu->id,
        'transaction_date' => '2026-03-11',
        'type' => 'expense',
        'amount' => 400000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $tet->id,
        'transaction_date' => '2026-03-12',
        'type' => 'income',
        'amount' => 750000,
        'status' => 'completed',
        'transaction_item' => 'Thu Tet',
    ]);

    Transaction::factory()->create([
        'category_id' => $trungThu->id,
        'transaction_date' => '2026-02-20',
        'type' => 'income',
        'amount' => 500000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu ngoai ky',
    ]);

    Transaction::factory()->create([
        'category_id' => $trungThu->id,
        'transaction_date' => '2025-08-10',
        'type' => 'income',
        'amount' => 900000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu nam ngoai',
    ]);

    Transaction::factory()->create([
        'category_id' => $trungThu->id,
        'transaction_date' => '2025-08-11',
        'type' => 'expense',
        'amount' => 200000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu nam ngoai',
    ]);

    Transaction::factory()->create([
        'category_id' => $tet->id,
        'transaction_date' => '2025-01-25',
        'type' => 'income',
        'amount' => 300000,
        'status' => 'completed',
        'transaction_item' => 'Thu Tet nam ngoai',
    ]);

    $this->actingAs($user);

    Livewire::test(CategoryAnalytics::class)
        ->set('selectedCategory', (string) $trungThu->id)
        ->set('dateRange.start', '2026-03-01')
        ->set('dateRange.end', '2026-03-31')
        ->assertSeeText(__('Previous year fund balance'))
        ->assertSeeText('700.000 đ')
        ->assertSeeText('Trung Thu')
        ->assertSeeText('1.000.000 đ')
        ->assertSeeText('400.000 đ')
        ->assertSeeText('600.000 đ')
        ->assertDontSeeText('750.000 đ')
        ->assertDontSeeText('500.000 đ');

    Carbon::setTestNow();
});
