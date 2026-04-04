<?php

use App\Livewire\Admin\Finance\Categories\CategoryAnalytics;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Transaction;
use App\Models\User;
use Flux\DateRange;
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
        ->assertSeeText(__('Total transactions'))
        ->assertSeeText('2')
        ->assertSeeText(__('So với năm trước'))
        ->assertSeeText(__('An toàn quỹ'))
        ->assertSeeText('01/03/2026 - 31/03/2026')
        ->assertSeeText('Trung Thu')
        ->assertSeeText('1.000.000 đ')
        ->assertSeeText('400.000 đ')
        ->assertSeeText('600.000 đ')
        ->assertDontSeeText('750.000 đ')
        ->assertDontSeeText('500.000 đ');

    Carbon::setTestNow();
});

test('category analytics memoizes category summaries per component instance', function () {
    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-03-10',
        'type' => 'income',
        'amount' => 1000000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu',
    ]);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(function (string $key, mixed $ttl, callable $resolver): bool {
            return str_contains($key, 'finance.category-analytics.v2.summaries.');
        })
        ->andReturnUsing(function (string $key, mixed $ttl, callable $resolver): mixed {
            return $resolver();
        });

    $component = new CategoryAnalytics;
    $component->dateRange = DateRange::yearToDate();

    $first = $component->categorySummaries();
    $second = $component->categorySummaries();

    expect($first)->toHaveCount(1)
        ->and($second)->toHaveCount(1)
        ->and($first->first()['name'])->toBe('Trung Thu')
        ->and($second->first()['name'])->toBe('Trung Thu');
});

test('category analytics compares the selected category by year across all data when no date range is applied', function () {
    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-09-10',
        'type' => 'income',
        'amount' => 800000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-09-12',
        'type' => 'expense',
        'amount' => 300000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-09-10',
        'type' => 'income',
        'amount' => 1200000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2026',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-09-12',
        'type' => 'expense',
        'amount' => 500000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu 2026',
    ]);

    $component = new CategoryAnalytics;
    $component->selectedCategory = (string) $category->id;
    $component->dateRange = null;

    $comparisonChart = $component->categoryComparisonChart();

    expect($comparisonChart['title'])->toBe(__('Income vs expense by year'))
        ->and($comparisonChart['description'])->toContain('Trung Thu')
        ->and($comparisonChart['points'])->toBe([
            [
                'category' => '2025',
                'income' => 800000,
                'expense' => 300000,
            ],
            [
                'category' => '2026',
                'income' => 1200000,
                'expense' => 500000,
            ],
        ]);
});

test('category analytics compares the selected category by year within the selected date range', function () {
    Carbon::setTestNow('2026-03-24 10:00:00');

    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Tet Nguyen Dan',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-02-10',
        'type' => 'income',
        'amount' => 800000,
        'status' => 'completed',
        'transaction_item' => 'Thu Tet 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-02-10',
        'type' => 'expense',
        'amount' => 500000,
        'status' => 'completed',
        'transaction_item' => 'Chi Tet 2026',
    ]);

    $component = new CategoryAnalytics;
    $component->selectedCategory = (string) $category->id;
    $component->dateRange = DateRange::yearToDate();

    $comparisonChart = $component->categoryComparisonChart();

    expect($comparisonChart['title'])->toBe(__('Income vs expense by year'))
        ->and($comparisonChart['description'])->toBe(__('Review yearly income and expense totals for :category within the selected date range.', [
            'category' => 'Tet Nguyen Dan',
        ]))
        ->and($comparisonChart['points'])->toBe([
            [
                'category' => '2026',
                'income' => 0,
                'expense' => 500000,
            ],
        ]);

    Carbon::setTestNow();
});

test('category analytics treats an empty date range as all time', function () {
    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-09-10',
        'type' => 'income',
        'amount' => 800000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-09-10',
        'type' => 'income',
        'amount' => 1200000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2026',
    ]);

    $component = new CategoryAnalytics;
    $component->selectedCategory = (string) $category->id;
    $component->dateRange = DateRange::yearToDate();
    $component->resetFilters();

    expect($component->dateRange)->toBeNull()
        ->and($component->categorySummaries()->sum('total_income'))->toBe(2000000);
});

test('category analytics calculates year over year insight for the same selected period', function () {
    Carbon::setTestNow('2026-04-04 10:00:00');

    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-03-10',
        'type' => 'income',
        'amount' => 1200000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2026',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-03-11',
        'type' => 'expense',
        'amount' => 500000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu 2026',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-03-10',
        'type' => 'income',
        'amount' => 1000000,
        'status' => 'completed',
        'transaction_item' => 'Thu Trung Thu 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-03-11',
        'type' => 'expense',
        'amount' => 400000,
        'status' => 'completed',
        'transaction_item' => 'Chi Trung Thu 2025',
    ]);

    $component = new CategoryAnalytics;
    $component->selectedCategory = (string) $category->id;
    $component->dateRange = new DateRange('2026-03-01', '2026-03-31');

    $insight = $component->yearOverYearInsight();

    expect($insight['current_label'])->toBe('01/03/2026 - 31/03/2026')
        ->and($insight['previous_label'])->toBe('01/03/2025 - 31/03/2025')
        ->and($insight['income_current'])->toBe(1200000)
        ->and($insight['income_previous'])->toBe(1000000)
        ->and($insight['expense_current'])->toBe(500000)
        ->and($insight['expense_previous'])->toBe(400000)
        ->and($insight['income_delta'])->toBe(200000)
        ->and($insight['expense_delta'])->toBe(100000)
        ->and($insight['income_delta_percentage'])->toBe(20.0)
        ->and($insight['expense_delta_percentage'])->toBe(25.0);

    Carbon::setTestNow();
});

test('category analytics estimates a safer spending reserve from recent annual expense history', function () {
    Carbon::setTestNow('2026-04-04 10:00:00');

    $category = Category::factory()->create([
        'ordering' => 1,
        'name' => 'Trại hè',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2023-07-10',
        'type' => 'expense',
        'amount' => 1000000,
        'status' => 'completed',
        'transaction_item' => 'Chi trại hè 2023',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2024-07-10',
        'type' => 'expense',
        'amount' => 1500000,
        'status' => 'completed',
        'transaction_item' => 'Chi trại hè 2024',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2025-07-10',
        'type' => 'expense',
        'amount' => 2000000,
        'status' => 'completed',
        'transaction_item' => 'Chi trại hè 2025',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_date' => '2026-01-10',
        'type' => 'income',
        'amount' => 5000000,
        'status' => 'completed',
        'transaction_item' => 'Thu quỹ trại hè',
    ]);

    $component = new CategoryAnalytics;
    $component->selectedCategory = (string) $category->id;
    $component->dateRange = DateRange::yearToDate();

    $insight = $component->fundSafetyInsight();

    expect($insight['average_annual_expense'])->toBe(1500000)
        ->and($insight['highest_annual_expense'])->toBe(2000000)
        ->and($insight['recommended_reserve'])->toBe(1700000)
        ->and($insight['current_balance'])->toBe(500000)
        ->and($insight['safe_to_spend'])->toBe(0)
        ->and($insight['status'])->toBe('watch')
        ->and($insight['years_used'])->toBe(['2023', '2024', '2025']);

    Carbon::setTestNow();
});
