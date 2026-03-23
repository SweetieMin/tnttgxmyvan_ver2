<?php

use App\Exports\Finance\TransactionExport as TransactionExcelExport;
use App\Livewire\Admin\Finance\Transactions\TransactionExport as TransactionExportComponent;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    Permission::findOrCreate('finance.transaction.view', 'web');
});

test('transaction export modal can be opened with current filters', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $category = Category::factory()->create();

    $this->actingAs($user);

    Livewire::test(TransactionExportComponent::class)
        ->dispatch('open-transaction-export-modal', selectedType: 'income', selectedCategory: (string) $category->id, selectedStatus: 'completed')
        ->assertSet('showExportModal', true)
        ->assertSet('selectedTypes', ['income'])
        ->assertSet('selectedCategoryIds', [(string) $category->id])
        ->assertSet('selectedStatuses', ['completed'])
        ->assertSet('fileName', fn (string $fileName): bool => str_starts_with($fileName, 'common-fund-transactions-'));
});

test('transaction export modal defaults to all selectable filters and columns', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $categories = Category::factory()->count(2)->create();
    $expectedCategoryIds = Category::query()
        ->orderBy('ordering')
        ->orderBy('name')
        ->pluck('id')
        ->map(fn (int $id): string => (string) $id)
        ->all();

    $this->actingAs($user);

    Livewire::test(TransactionExportComponent::class)
        ->dispatch('open-transaction-export-modal')
        ->assertSet('selectedTypes', ['income', 'expense'])
        ->assertSet('selectedStatuses', ['pending', 'completed'])
        ->assertSet('selectedCategoryIds', $expectedCategoryIds)
        ->assertSet('selectedColumns', ['transaction_date', 'category', 'transaction_item', 'amount', 'in_charge', 'status'])
        ->assertSet('fileName', fn (string $fileName): bool => str_starts_with($fileName, 'common-fund-transactions-'))
        ->assertSee(__('Leave the filters empty to export all transactions.'));
});

test('transaction export modal keeps the export note visible when filters are selected', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $category = Category::factory()->create();

    $this->actingAs($user);

    Livewire::test(TransactionExportComponent::class)
        ->dispatch('open-transaction-export-modal', selectedType: 'income', selectedCategory: (string) $category->id, selectedStatus: 'completed')
        ->assertSee(__('Leave the filters empty to export all transactions.'));
});

test('transactions can be exported to excel with split amount columns and summary rows', function () {
    $category = Category::factory()->create([
        'name' => 'Trung Thu',
    ]);

    $incomeTransaction = Transaction::factory()->create([
        'transaction_date' => '2026-03-20',
        'category_id' => $category->id,
        'transaction_item' => 'Thu Trung Thu',
        'type' => 'income',
        'amount' => 1200000,
        'in_charge' => 'Ban quy',
        'status' => 'completed',
    ]);

    $expenseTransaction = Transaction::factory()->create([
        'transaction_date' => '2026-03-19',
        'category_id' => $category->id,
        'transaction_item' => 'Chi Trung Thu',
        'type' => 'expense',
        'amount' => 450000,
        'in_charge' => 'Ban hau can',
        'status' => 'completed',
    ]);

    $export = new TransactionExcelExport(
        transactions: collect([$incomeTransaction, $expenseTransaction]),
        selectedColumns: ['transaction_date', 'category', 'transaction_item', 'amount', 'in_charge', 'status'],
    );

    $temporaryFile = tempnam(sys_get_temp_dir(), 'transaction-export-');

    file_put_contents($temporaryFile, Excel::raw($export, Maatwebsite\Excel\Excel::XLSX));

    $spreadsheet = IOFactory::load($temporaryFile);
    $sheet = $spreadsheet->getActiveSheet();

    expect([
        $sheet->getCell('A1')->getValue(),
        $sheet->getCell('B1')->getValue(),
        $sheet->getCell('C1')->getValue(),
        $sheet->getCell('D1')->getValue(),
        $sheet->getCell('E1')->getValue(),
        $sheet->getCell('F1')->getValue(),
        $sheet->getCell('G1')->getValue(),
    ])->toBe([
        'Ngày giao dịch',
        'Hạng mục',
        'Khoản mục chi',
        'Thu',
        'Chi',
        'Người phụ trách',
        'Trạng thái',
    ]);

    expect($sheet->getCell('D2')->getValue())->toBe(1200000)
        ->and($sheet->getCell('E2')->getValue())->toBeNull()
        ->and($sheet->getCell('D3')->getValue())->toBeNull()
        ->and($sheet->getCell('E3')->getValue())->toBe(450000)
        ->and($sheet->getCell('D4')->getValue())->toBe(1200000)
        ->and($sheet->getCell('E4')->getValue())->toBe(450000)
        ->and($sheet->getCell('D5')->getValue())->toBe('Tiền còn lại = 750.000')
        ->and($sheet->getMergeCells())->toContain('D5:E5');

    @unlink($temporaryFile);
});

test('transaction export component downloads an excel export with the custom file name', function () {
    Excel::fake();
    Date::setTestNow('2026-03-23 09:15:30');

    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $category = Category::factory()->create([
        'name' => 'Trung Thu',
    ]);

    Transaction::factory()->create([
        'category_id' => $category->id,
        'transaction_item' => 'Thu Trung Thu',
        'type' => 'income',
        'status' => 'completed',
        'amount' => 1000000,
        'transaction_date' => '2026-03-20',
    ]);

    $this->actingAs($user);

    Livewire::test(TransactionExportComponent::class)
        ->dispatch('open-transaction-export-modal')
        ->set('selectedTypes', ['income'])
        ->set('selectedCategoryIds', [(string) $category->id])
        ->set('selectedStatuses', ['completed'])
        ->set('selectedColumns', ['transaction_date', 'category', 'transaction_item', 'amount'])
        ->set('fileName', 'bao-cao-quy-thang-3')
        ->set('dateRange', [
            'start' => '2026-03-01',
            'end' => '2026-03-31',
        ])
        ->call('exportTransactions');

    Excel::assertDownloaded('bao-cao-quy-thang-3.xlsx', function (TransactionExcelExport $export): bool {
        return $export->headings() === ['Ngày giao dịch', 'Hạng mục', 'Khoản mục chi', 'Thu', 'Chi']
            && count($export->dataRows()) === 1;
    });
});
