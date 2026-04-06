<?php

namespace Database\Seeders;

use App\Models\Regulation;
use Illuminate\Database\Seeder;

class RegulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $baseData = [
            // === ÁP DỤNG CHUNG CHO CẢ 4 ĐỐI TƯỢNG ===
            [
                'ordering' => 1,
                'description' => 'Tham dự Thánh Lễ Chúa Nhật hàng tuần - Tham dự Thánh Lễ Trọng (cả buộc và không buộc) - Tham dự Thánh Lễ Khác theo yêu cầu của đoàn TNTT (bổn mạng,..)',
                'type' => 'plus',
                'points' => 3,
                'status' => 'applied',
                'short_desc' => 'Tham dự Thánh Lễ',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 2,
                'description' => 'Tham dự các giờ Chầu lượt, Tĩnh Tâm Thiếu Nhi',
                'type' => 'plus',
                'points' => 3,
                'status' => 'applied',
                'short_desc' => 'Tham dự chầu lượt',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 3,
                'description' => 'Tham dự các giờ Chầu Thánh Thể',
                'type' => 'plus',
                'points' => 3,
                'status' => 'applied',
                'short_desc' => 'Tham dự giờ chầu',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 4,
                'description' => 'Tham gia các hoạt động sinh hoạt ngoại khóa, trại hè, đá bóng, chiến dịch do xứ đoàn phát động',
                'type' => 'plus',
                'points' => 5,
                'status' => 'applied',
                'short_desc' => 'Tham gia sinh hoạt',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 5,
                'description' => 'Tham gia các đội văn nghệ, đội lân, đội trống',
                'type' => 'plus',
                'points' => 5,
                'status' => 'applied',
                'short_desc' => 'Tham gia sinh hoạt',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 6,
                'description' => 'Không tham gia các khóa học huấn luyện (*)',
                'type' => 'minus',
                'points' => 20,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 7,
                'description' => 'Không phép: Không tham gia các chiến dịch bao gồm công tác chuẩn bị, lịch họp. Tham gia không nghiêm túc, sử dụng điện thoại gây mất tập trung (*)',
                'type' => 'minus',
                'points' => 3,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 8,
                'description' => 'Không tham gia các hoạt động cắm trại và đá bóng',
                'type' => 'minus',
                'points' => 10,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 9,
                'description' => 'Không thực hiện nhiệm vụ được giao hoặc thực hiện không đúng yêu cầu, không đúng hạn. (*)',
                'type' => 'minus',
                'points' => 3,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 10,
                'description' => 'Có hành vi gây chia rẽ, mất đoàn kết trong đoàn, làm ảnh hưởng đến tinh thần chung',
                'type' => 'minus',
                'points' => 10,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 11,
                'description' => 'Có hành vi vô lễ, không tôn trọng người khác, có thái độ kiêu ngạo, cứng đầu',
                'type' => 'minus',
                'points' => 10,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 12,
                'description' => 'Không ngồi đúng chỗ ngồi - Không mặc đúng đồng phục TNTT',
                'type' => 'minus',
                'points' => 2,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'ordering' => 13,
                'description' => 'Sử dụng lời lẽ tục tĩu, chửi thề, đánh nhau,... gây mất đoàn kết trong khuôn viên nhà thờ, nhà xứ, nhà giáo lý',
                'type' => 'minus',
                'points' => 10,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 14,
                'description' => 'Xả rác không đúng nơi quy định, phá hoại môi trường trong khuôn viên nhà thờ, nhà xứ, nhà giáo lý',
                'type' => 'minus',
                'points' => 5,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'ordering' => 15,
                'description' => 'Tham gia vào các tệ nạn xã hội (cờ bạc, hút thuốc lá điện tử, trộm cắp,...), các hành vi phá hoại của công',
                'type' => 'minus',
                'points' => 20,
                'status' => 'applied',
                'short_desc' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($baseData as $item) {
            $item['short_desc'] ??= $item['description'];

            Regulation::query()->updateOrCreate(
                ['ordering' => $item['ordering']],
                $item,
            );
        }
    }
}
