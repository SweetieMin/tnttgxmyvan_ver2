<?php

use App\Models\Regulation;
use Database\Seeders\RegulationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('regulation seeder matches the current disciplinary regulations', function () {
    $this->seed(RegulationSeeder::class);

    expect(Regulation::query()->count())->toBe(15);

    $expectedRegulations = [
        6 => ['Không tham gia các khóa học huấn luyện (*)', 20, 'applied'],
        7 => ['Không phép: Không tham gia các chiến dịch bao gồm công tác chuẩn bị, lịch họp. Tham gia không nghiêm túc, sử dụng điện thoại gây mất tập trung (*)', 3, 'applied'],
        8 => ['Không tham gia các hoạt động cắm trại và đá bóng', 10, 'applied'],
        9 => ['Không thực hiện nhiệm vụ được giao hoặc thực hiện không đúng yêu cầu, không đúng hạn. (*)', 3, 'applied'],
        10 => ['Có hành vi gây chia rẽ, mất đoàn kết trong đoàn, làm ảnh hưởng đến tinh thần chung', 10, 'applied'],
        11 => ['Có hành vi vô lễ, không tôn trọng người khác, có thái độ kiêu ngạo, cứng đầu', 10, 'applied'],
        12 => ['Không ngồi đúng chỗ ngồi - Không mặc đúng đồng phục TNTT', 2, 'applied'],
        13 => ['Sử dụng lời lẽ tục tĩu, chửi thề, đánh nhau,... gây mất đoàn kết trong khuôn viên nhà thờ, nhà xứ, nhà giáo lý', 10, 'applied'],
        14 => ['Xả rác không đúng nơi quy định, phá hoại môi trường trong khuôn viên nhà thờ, nhà xứ, nhà giáo lý', 5, 'applied'],
        15 => ['Tham gia vào các tệ nạn xã hội (cờ bạc, hút thuốc lá điện tử, trộm cắp,...), các hành vi phá hoại của công', 20, 'applied'],
    ];

    foreach ($expectedRegulations as $ordering => [$description, $points, $status]) {
        $regulation = Regulation::query()->where('ordering', $ordering)->first();

        expect($regulation)
            ->not->toBeNull()
            ->and($regulation->description)->toBe($description)
            ->and($regulation->points)->toBe($points)
            ->and($regulation->status)->toBe($status)
            ->and($regulation->type)->toBe('minus');
    }
});
