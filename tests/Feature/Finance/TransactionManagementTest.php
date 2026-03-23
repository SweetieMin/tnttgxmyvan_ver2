<?php

use App\Livewire\Admin\Finance\Transactions\TransactionActions;
use App\Livewire\Admin\Finance\Transactions\TransactionList;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');

    collect([
        'finance.transaction.view',
        'finance.transaction.create',
        'finance.transaction.update',
        'finance.transaction.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the common fund page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $response = $this->actingAs($user)->get(route('admin.finance.transactions'));

    $response->assertOk()
        ->assertSeeText(__('Common fund'));
});

test('transactions can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'finance.transaction.view',
        'finance.transaction.create',
        'finance.transaction.update',
        'finance.transaction.delete',
    ]);

    $this->actingAs($user);

    $receipt = UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf');
    $category = Category::factory()->create([
        'name' => 'Tet',
        'ordering' => 1,
    ]);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->set('transaction_date', '2026-03-21')
        ->set('category_id', $category->id)
        ->set('transaction_item', 'Thu quỹ đầu năm')
        ->set('description', 'Phụ huynh đóng quỹ chung')
        ->set('type', 'income')
        ->set('amount', 500000)
        ->set('in_charge', 'Ban tài chính')
        ->set('status', 'completed')
        ->set('attachment', $receipt)
        ->call('saveTransaction')
        ->assertHasNoErrors();

    $transaction = Transaction::query()->where('transaction_item', 'Thu quỹ đầu năm')->firstOrFail();

    expect($transaction->category_id)->toBe($category->id);
    expect($transaction->file_name)->toStartWith('files/transactions/TRANSACTION-');
    Storage::disk('public')->assertExists($transaction->file_name);

    $oldFile = $transaction->file_name;
    $invoice = UploadedFile::fake()->create('invoice.pdf', 200, 'application/pdf');

    Livewire::test(TransactionActions::class)
        ->call('openEditModal', $transaction->id)
        ->set('amount', 650000)
        ->set('attachment', $invoice)
        ->call('saveTransaction')
        ->assertHasNoErrors();

    $transaction->refresh();

    expect($transaction->amount)->toBe(650000);
    expect($transaction->file_name)->not->toBe($oldFile);
    Storage::disk('public')->assertExists($transaction->file_name);
    Storage::disk('public')->assertMissing($oldFile);

    $currentFile = $transaction->file_name;

    Livewire::test(TransactionActions::class)
        ->call('confirmDeleteTransaction', $transaction->id)
        ->call('deleteTransaction')
        ->assertHasNoErrors();

    expect(Transaction::query()->whereKey($transaction->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($currentFile);
});

test('transaction save button only appears after the form changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'finance.transaction.create',
        'finance.transaction.update',
    ]);

    $transaction = Transaction::factory()->create();

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->call('shouldShowSaveTransactionButton')
        ->assertReturned(false)
        ->set('transaction_item', 'Chi mua tập')
        ->call('shouldShowSaveTransactionButton')
        ->assertReturned(true);

    Livewire::test(TransactionActions::class)
        ->call('openEditModal', $transaction->id)
        ->call('shouldShowSaveTransactionButton')
        ->assertReturned(false)
        ->set('amount', $transaction->amount + 10000)
        ->call('shouldShowSaveTransactionButton')
        ->assertReturned(true);
});

test('transaction form shows active categories and requires category selection', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    Category::factory()->create([
        'name' => 'Tet',
        'ordering' => 1,
        'is_active' => true,
    ]);

    Category::factory()->create([
        'name' => 'Inactive category',
        'ordering' => 2,
        'is_active' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->assertSeeText('Tet')
        ->assertDontSeeText('Inactive category')
        ->set('transaction_date', '2026-03-21')
        ->set('transaction_item', 'Chi lẻ')
        ->set('type', 'expense')
        ->set('amount', 100000)
        ->set('status', 'completed')
        ->call('saveTransaction')
        ->assertHasErrors(['category_id' => 'required']);
});

test('transaction amount accepts masked thousands separators', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    $category = Category::factory()->create([
        'name' => 'Tet',
        'ordering' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->set('transaction_date', '2026-03-21')
        ->set('category_id', $category->id)
        ->set('transaction_item', 'Thu quy tu mask')
        ->set('type', 'income')
        ->set('amount', '1,231,231,111')
        ->set('status', 'completed')
        ->call('saveTransaction')
        ->assertHasNoErrors();

    expect(Transaction::query()->where('transaction_item', 'Thu quy tu mask')->value('amount'))
        ->toBe(1231231111);
});

test('transactions can be filtered by category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.view');

    $tet = Category::factory()->create([
        'name' => 'Tet',
        'ordering' => 1,
    ]);

    $trungThu = Category::factory()->create([
        'name' => 'Trung Thu',
        'ordering' => 2,
    ]);

    Transaction::factory()->create([
        'transaction_item' => 'Thu Tet',
        'category_id' => $tet->id,
    ]);

    Transaction::factory()->create([
        'transaction_item' => 'Thu Trung Thu',
        'category_id' => $trungThu->id,
    ]);

    $this->actingAs($user);

    Livewire::test(TransactionList::class, [
        'selectedCategory' => (string) $tet->id,
    ])
        ->assertSeeText('Thu Tet')
        ->assertDontSeeText('Thu Trung Thu');
});

test('selected transaction attachment can be removed before saving', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->set('attachment', UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf'))
        ->assertSet('attachment', fn ($attachment) => $attachment !== null)
        ->call('removeSelectedAttachment')
        ->assertSet('attachment', null);
});

test('transaction attachment only accepts pdf files', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    $category = Category::factory()->create([
        'name' => 'Tet',
        'ordering' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->set('transaction_date', '2026-03-21')
        ->set('category_id', $category->id)
        ->set('transaction_item', 'Thu quỹ đầu năm')
        ->set('type', 'income')
        ->set('amount', 500000)
        ->set('status', 'completed')
        ->set('attachment', UploadedFile::fake()->image('receipt.png'))
        ->call('saveTransaction')
        ->assertHasErrors(['attachment']);
});

test('transaction item field shows existing unique suggestions in autocomplete', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    Transaction::factory()->create(['transaction_item' => 'Thu quỹ đầu năm']);
    Transaction::factory()->create(['transaction_item' => 'Thu quỹ đầu năm']);
    Transaction::factory()->create(['transaction_item' => 'Chi sinh hoạt thiếu nhi']);

    $this->actingAs($user);

    Livewire::test(TransactionActions::class)
        ->call('openCreateModal')
        ->assertSeeText('Thu quỹ đầu năm')
        ->assertSeeText('Chi sinh hoạt thiếu nhi');
});

test('transaction item autocomplete suggestions are limited to 20 values', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.transaction.create');

    foreach (range(1, 25) as $index) {
        Transaction::factory()->create([
            'transaction_item' => 'Khoản mục '.$index,
        ]);
    }

    $this->actingAs($user);

    $component = Livewire::test(TransactionActions::class)
        ->call('openCreateModal');

    expect($component->instance()->transactionItemSuggestions())->toHaveCount(20)
        ->toContain('Khoản mục 25')
        ->not->toContain('Khoản mục 1');
});
