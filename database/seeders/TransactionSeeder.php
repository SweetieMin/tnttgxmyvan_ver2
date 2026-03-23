<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = CarbonImmutable::today();

        $transactionsByCategory = [
            'Bổn Mạng' => [
                [
                    'transaction_date' => '2025-08-10',
                    'transaction_item' => 'Quyên góp Bổn Mạng',
                    'description' => 'Đóng góp cho lễ Bổn Mạng của đoàn.',
                    'type' => 'income',
                    'amount' => 2800000,
                    'in_charge' => 'Ban tài chính',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-08-18',
                    'transaction_item' => 'Chi trang trí Bổn Mạng',
                    'description' => 'Chi mua hoa, băng rôn và vật dụng trang trí cho lễ Bổn Mạng.',
                    'type' => 'expense',
                    'amount' => 1850000,
                    'in_charge' => 'Ban hậu cần',
                    'status' => 'completed',
                ],
            ],
            'Trung Thu' => [
                [
                    'transaction_date' => '2025-09-28',
                    'transaction_item' => 'Ủng hộ chương trình Trung Thu',
                    'description' => 'Ân nhân hỗ trợ cho chương trình Trung Thu thiếu nhi.',
                    'type' => 'income',
                    'amount' => 4200000,
                    'in_charge' => 'Ban gây quỹ',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-09-30',
                    'transaction_item' => 'Chi quà Trung Thu',
                    'description' => 'Chi mua lồng đèn, bánh và phần quà cho đoàn sinh.',
                    'type' => 'expense',
                    'amount' => 3980000,
                    'in_charge' => 'Ban thiếu nhi',
                    'status' => 'completed',
                ],
            ],
            'Giáng Sinh' => [
                [
                    'transaction_date' => '2025-12-10',
                    'transaction_item' => 'Tài trợ chương trình Giáng Sinh',
                    'description' => 'Nhận tài trợ cho hoạt động diễn nguyện và phát quà Giáng Sinh.',
                    'type' => 'income',
                    'amount' => 5100000,
                    'in_charge' => 'Ban gây quỹ',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-12-22',
                    'transaction_item' => 'Chi trang trí Giáng Sinh',
                    'description' => 'Chi làm hang đá, đèn và vật phẩm trang trí đêm Giáng Sinh.',
                    'type' => 'expense',
                    'amount' => 4675000,
                    'in_charge' => 'Ban phụng vụ',
                    'status' => 'completed',
                ],
            ],
            'Tết niên' => [
                [
                    'transaction_date' => '2025-12-27',
                    'transaction_item' => 'Đóng góp tiệc Tất niên',
                    'description' => 'Đóng góp tổ chức buổi gặp mặt Tất niên của xứ đoàn.',
                    'type' => 'income',
                    'amount' => 3200000,
                    'in_charge' => 'Ban điều hành',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-12-29',
                    'transaction_item' => 'Chi tiệc Tất niên',
                    'description' => 'Chi thực phẩm, nước uống và hậu cần cho buổi Tất niên.',
                    'type' => 'expense',
                    'amount' => 3560000,
                    'in_charge' => 'Ban hậu cần',
                    'status' => 'completed',
                ],
            ],
            'Tết Nguyên Đán' => [
                [
                    'transaction_date' => '2025-01-19',
                    'transaction_item' => 'Mừng tuổi quỹ Tết',
                    'description' => 'Đóng góp quỹ lì xì và hỗ trợ đầu năm mới.',
                    'type' => 'income',
                    'amount' => 2750000,
                    'in_charge' => 'Ban tài chính',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2026-02-08',
                    'transaction_item' => 'Chi thăm viếng Tết',
                    'description' => 'Chi quà thăm hỏi huynh trưởng và gia đình khó khăn dịp Tết.',
                    'type' => 'expense',
                    'amount' => 3380000,
                    'in_charge' => 'Ban bác ái',
                    'status' => 'completed',
                ],
            ],
            'Tĩnh Tâm' => [
                [
                    'transaction_date' => '2025-03-09',
                    'transaction_item' => 'Đóng góp ngày Tĩnh Tâm',
                    'description' => 'Đóng góp tổ chức chương trình Tĩnh Tâm Mùa Chay.',
                    'type' => 'income',
                    'amount' => 1900000,
                    'in_charge' => 'Ban điều hành',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2026-03-15',
                    'transaction_item' => 'Chi hậu cần Tĩnh Tâm',
                    'description' => 'Chi nước uống, tài liệu và âm thanh cho ngày Tĩnh Tâm.',
                    'type' => 'expense',
                    'amount' => 2140000,
                    'in_charge' => 'Ban phụng vụ',
                    'status' => 'completed',
                ],
            ],
            'Ngày của Cha & Mẹ' => [
                [
                    'transaction_date' => '2025-05-11',
                    'transaction_item' => 'Quỹ quà Ngày của Mẹ',
                    'description' => 'Nhận đóng góp cho chương trình chúc mừng Ngày của Mẹ.',
                    'type' => 'income',
                    'amount' => 1650000,
                    'in_charge' => 'Ban thiếu nhi',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-06-15',
                    'transaction_item' => 'Chi quà Ngày của Cha',
                    'description' => 'Chi quà lưu niệm và thiệp cho chương trình Ngày của Cha.',
                    'type' => 'expense',
                    'amount' => 1480000,
                    'in_charge' => 'Ban thiếu nhi',
                    'status' => 'completed',
                ],
            ],
            'Trại Ấi' => [
                [
                    'transaction_date' => '2025-06-08',
                    'transaction_item' => 'Phí tham gia Trại Ấi',
                    'description' => 'Thu phí tham gia Trại Ấi của đoàn sinh.',
                    'type' => 'income',
                    'amount' => 4600000,
                    'in_charge' => 'Ban trại',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-06-20',
                    'transaction_item' => 'Chi vật dụng Trại Ấi',
                    'description' => 'Chi lều bạt, dây buộc và vật dụng sinh hoạt cho Trại Ấi.',
                    'type' => 'expense',
                    'amount' => 4390000,
                    'in_charge' => 'Ban hậu cần',
                    'status' => 'completed',
                ],
            ],
            'Trại Thiếu' => [
                [
                    'transaction_date' => '2025-08-03',
                    'transaction_item' => 'Phí tham gia Trại Thiếu',
                    'description' => 'Thu phí tham gia Trại Thiếu của ngành Thiếu.',
                    'type' => 'income',
                    'amount' => 5400000,
                    'in_charge' => 'Ban trại',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-08-11',
                    'transaction_item' => 'Chi hậu cần Trại Thiếu',
                    'description' => 'Chi ăn uống, y tế và vận chuyển cho Trại Thiếu.',
                    'type' => 'expense',
                    'amount' => 5760000,
                    'in_charge' => 'Ban hậu cần',
                    'status' => 'completed',
                ],
            ],
            'Đá bóng' => [
                [
                    'transaction_date' => '2025-07-06',
                    'transaction_item' => 'Tài trợ giải bóng đá',
                    'description' => 'Nhận tài trợ giải bóng đá giao lưu mùa hè.',
                    'type' => 'income',
                    'amount' => 2500000,
                    'in_charge' => 'Ban thể thao',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-07-20',
                    'transaction_item' => 'Chi sân bãi đá bóng',
                    'description' => 'Chi thuê sân, nước uống và bóng thi đấu.',
                    'type' => 'expense',
                    'amount' => 2870000,
                    'in_charge' => 'Ban thể thao',
                    'status' => 'completed',
                ],
            ],
            'Du lịch' => [
                [
                    'transaction_date' => '2025-07-27',
                    'transaction_item' => 'Thu chuyến du lịch hè',
                    'description' => 'Thu phí chuyến du lịch hè dành cho huynh trưởng và đoàn sinh.',
                    'type' => 'income',
                    'amount' => 6300000,
                    'in_charge' => 'Ban điều hành',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-07-30',
                    'transaction_item' => 'Chi xe du lịch hè',
                    'description' => 'Chi thuê xe và nước uống cho chuyến du lịch hè.',
                    'type' => 'expense',
                    'amount' => 6120000,
                    'in_charge' => 'Ban hậu cần',
                    'status' => 'completed',
                ],
            ],
            'Hỗ trợ trại huấn luyện' => [
                [
                    'transaction_date' => '2025-08-14',
                    'transaction_item' => 'Quỹ hỗ trợ trại huấn luyện',
                    'description' => 'Nhận đóng góp hỗ trợ huynh trưởng tham gia trại huấn luyện.',
                    'type' => 'income',
                    'amount' => 3000000,
                    'in_charge' => 'Ban huấn luyện',
                    'status' => 'completed',
                ],
                [
                    'transaction_date' => '2025-08-21',
                    'transaction_item' => 'Chi hỗ trợ trại huấn luyện',
                    'description' => 'Chi hỗ trợ học phí và di chuyển cho huynh trưởng dự trại huấn luyện.',
                    'type' => 'expense',
                    'amount' => 3280000,
                    'in_charge' => 'Ban huấn luyện',
                    'status' => 'completed',
                ],
            ],
        ];

        $categories = Category::query()
            ->whereIn('name', array_keys($transactionsByCategory))
            ->get()
            ->keyBy('name');

        foreach ($transactionsByCategory as $categoryName => $transactions) {
            /** @var Category|null $category */
            $category = $categories->get($categoryName);

            if (! $category) {
                continue;
            }

            foreach ($transactions as $attributes) {
                $transactionDate = CarbonImmutable::parse($attributes['transaction_date']);

                if ($transactionDate->lt(CarbonImmutable::parse('2025-01-01')) || $transactionDate->gt($today)) {
                    continue;
                }

                Transaction::query()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'transaction_date' => $transactionDate->toDateString(),
                        'transaction_item' => $attributes['transaction_item'],
                    ],
                    [
                        'description' => $attributes['description'],
                        'type' => $attributes['type'],
                        'amount' => $attributes['amount'],
                        'file_name' => null,
                        'in_charge' => $attributes['in_charge'],
                        'status' => $attributes['status'],
                    ],
                );
            }
        }
    }
}
