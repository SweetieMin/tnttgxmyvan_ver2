<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonnelRosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->roster() as $member) {
            $user = $this->upsertUser($member);
            $user->syncRoles([$this->role($member['role'])]);
        }
    }

    /**
     * @return array<int, array{
     *     christian_name: string,
     *     last_name: string,
     *     name: string,
     *     username: string,
     *     birthday: string,
     *     password: string,
     *     role: string,
     *     email?: string
     * }>
     */
    protected function roster(): array
    {
        return [
            [
                'christian_name' => 'Phêrô',
                'last_name' => 'Trần Văn',
                'name' => 'Minh',
                'username' => 'MV15067501',
                'birthday' => '1975-06-15',
                'email' => 'linhhuong.myvan@example.com',
                'password' => '12345',
                'role' => 'Cha Tuyên Úy',
            ],
            [
                'christian_name' => 'Gioan Baotixita',
                'last_name' => 'Lê Quang',
                'name' => 'Huy',
                'username' => 'MV11088202',
                'birthday' => '1982-08-11',
                'email' => 'phote.myvan@example.com',
                'password' => '12345',
                'role' => 'Thầy Phó Tế',
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Phạm Ngọc',
                'name' => 'Quỳnh',
                'username' => 'MV14090524',
                'birthday' => '2005-09-14',
                'password' => '12345',
                'role' => 'Trưởng Giáo Lý',
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Nguyễn Thị Bích',
                'name' => 'Liên',
                'username' => 'MV22109686',
                'birthday' => '1996-10-22',
                'password' => '12345',
                'role' => 'Phó Giáo Lý',
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Vũ Minh',
                'name' => 'Đức',
                'username' => 'MV01109999',
                'birthday' => '1999-10-01',
                'password' => '12345',
                'role' => 'Xứ Đoàn Trưởng',
            ],
            [
                'christian_name' => 'Teresa',
                'last_name' => 'Nguyễn Thị Ngọc',
                'name' => 'Vân',
                'username' => 'MV19089999',
                'birthday' => '1999-08-19',
                'password' => '12345',
                'role' => 'Xứ Đoàn Phó',
            ],
            [
                'christian_name' => 'Đinh Sơn',
                'last_name' => 'Đoàn Trường',
                'name' => 'Nam',
                'username' => 'MV21099898',
                'birthday' => '1998-09-21',
                'password' => '12345',
                'role' => 'Trưởng Ngành Nghĩa',
            ],
            [
                'christian_name' => 'Maria Monica',
                'last_name' => 'Nguyễn Thị Kim',
                'name' => 'Anh',
                'username' => 'MV26089924',
                'birthday' => '1999-08-26',
                'password' => '12345',
                'role' => 'Phó Ngành Nghĩa',
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Nguyễn Khắc',
                'name' => 'Huấn',
                'username' => 'MV19019797',
                'birthday' => '1997-01-19',
                'email' => 'nguyenkhachuan1997@gmail.com',
                'password' => '12345',
                'role' => 'Trưởng Ngành Thiếu',
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Vũ Hồng',
                'name' => 'Phúc',
                'username' => 'MV03010574',
                'birthday' => '2005-01-03',
                'password' => '12345',
                'role' => 'Phó Ngành Thiếu',
            ],
            [
                'christian_name' => 'Monica',
                'last_name' => 'Nguyễn Hoàng Kim',
                'name' => 'Dung',
                'username' => 'MV26030101',
                'birthday' => '2001-03-26',
                'password' => '12345',
                'role' => 'Trưởng Ngành Ấu',
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Vũ Tấn',
                'name' => 'Lộc',
                'username' => 'MV01070274',
                'birthday' => '2002-07-01',
                'password' => '12345',
                'role' => 'Phó Ngành Ấu',
            ],
            [
                'christian_name' => 'Teresa',
                'last_name' => 'Nguyễn Thị Thúy',
                'name' => 'Vy',
                'username' => 'MV11100101',
                'birthday' => '2001-10-11',
                'password' => '12345',
                'role' => 'Trưởng Ngành Tiền Ấu',
            ],
            [
                'christian_name' => 'Anna',
                'last_name' => 'Đỗ Ngọc',
                'name' => 'Mai',
                'username' => 'MV12060325',
                'birthday' => '2003-06-12',
                'password' => '12345',
                'role' => 'Phó Ngành Tiền Ấu',
            ],
            ...$this->membersForRole(
                role: 'Giáo Lý Viên',
                christianNames: ['Phêrô', 'Phaolô', 'Têrêsa', 'Maria', 'Anna', 'Giuse', 'Mônica', 'Tôma', 'Gioan', 'Luca', 'Phanxicô', 'Cecilia'],
                lastNames: ['Nguyễn Văn', 'Trần Quốc', 'Lê Minh', 'Phạm Đức', 'Hoàng Gia', 'Bùi Hải', 'Đặng Khánh', 'Võ Thành', 'Phan Hữu', 'Ngô Anh', 'Đỗ Minh', 'Mai Ngọc'],
                names: ['An', 'Bảo', 'Châu', 'Duy', 'Giang', 'Hải', 'Khanh', 'Linh', 'Minh', 'Ngân', 'Phương', 'Trang'],
                usernames: ['MV01020001', 'MV01020002', 'MV01020003', 'MV01020004', 'MV01020005', 'MV01020006', 'MV01020007', 'MV01020008', 'MV01020009', 'MV01020010', 'MV01020011', 'MV01020012'],
                birthdayYear: 2000,
            ),
            ...$this->membersForRole(
                role: 'Huynh Trưởng',
                christianNames: ['Phêrô', 'Phaolô', 'Têrêsa', 'Maria', 'Anna', 'Giuse', 'Mônica', 'Tôma', 'Gioan', 'Luca', 'Phanxicô', 'Cecilia'],
                lastNames: ['Nguyễn Gia', 'Trần Minh', 'Lê Nhật', 'Phạm Quang', 'Hoàng Mỹ', 'Bùi Quốc', 'Đặng Thanh', 'Võ Hoài', 'Phan Minh', 'Ngô Bảo', 'Đỗ Tuấn', 'Mai Đức'],
                names: ['Bình', 'Chi', 'Đạt', 'Hân', 'Khải', 'Lam', 'My', 'Nhi', 'Phong', 'Quân', 'Thảo', 'Yến'],
                usernames: ['MV01030001', 'MV01030002', 'MV01030003', 'MV01030004', 'MV01030005', 'MV01030006', 'MV01030007', 'MV01030008', 'MV01030009', 'MV01030010', 'MV01030011', 'MV01030012'],
                birthdayYear: 2002,
            ),
            ...$this->membersForRole(
                role: 'Dự Trưởng',
                christianNames: ['Phêrô', 'Phaolô', 'Têrêsa', 'Maria', 'Anna', 'Giuse', 'Mônica', 'Tôma', 'Gioan', 'Luca', 'Phanxicô', 'Cecilia'],
                lastNames: ['Nguyễn Hữu', 'Trần Hồng', 'Lê Công', 'Phạm Kim', 'Hoàng Diệu', 'Bùi Trọng', 'Đặng Thu', 'Võ Quốc', 'Phan Tuấn', 'Ngô Hồng', 'Đỗ Thanh', 'Mai Khánh'],
                names: ['Băng', 'Đăng', 'Hiếu', 'Khoa', 'Lan', 'Long', 'Ngọc', 'Như', 'Phú', 'Tâm', 'Uyên', 'Vũ'],
                usernames: ['MV01040001', 'MV01040002', 'MV01040003', 'MV01040004', 'MV01040005', 'MV01040006', 'MV01040007', 'MV01040008', 'MV01040009', 'MV01040010', 'MV01040011', 'MV01040012'],
                birthdayYear: 2004,
            ),
            ...$this->membersForRole(
                role: 'Thiếu Nhi',
                christianNames: ['Phêrô', 'Phaolô', 'Têrêsa', 'Maria', 'Anna', 'Giuse', 'Mônica', 'Tôma', 'Gioan', 'Luca', 'Phanxicô', 'Cecilia', 'Rosa', 'Marta', 'Gianna', 'Clara', 'Martinô', 'Vinh Sơn', 'Đaminh', 'Inhaxiô', 'Carôlô', 'Augustinô', 'Tadeo', 'Simon'],
                lastNames: ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Bùi', 'Đặng', 'Võ', 'Phan', 'Ngô', 'Đỗ', 'Mai', 'Dương', 'Lý', 'Tạ', 'Hồ', 'Trương', 'Chu', 'Tôn', 'Hứa', 'Trịnh', 'Tăng', 'Cao', 'Lương'],
                names: ['An', 'Bình', 'Chi', 'Dương', 'Giang', 'Hà', 'Khang', 'Khánh', 'Lâm', 'Mai', 'Minh', 'My', 'Nam', 'Ngân', 'Nhi', 'Phúc', 'Quỳnh', 'Sơn', 'Thảo', 'Trang', 'Trí', 'Uyên', 'Vi', 'Vy'],
                usernames: ['MV01050001', 'MV01050002', 'MV01050003', 'MV01050004', 'MV01050005', 'MV01050006', 'MV01050007', 'MV01050008', 'MV01050009', 'MV01050010', 'MV01050011', 'MV01050012', 'MV01050013', 'MV01050014', 'MV01050015', 'MV01050016', 'MV01050017', 'MV01050018', 'MV01050019', 'MV01050020', 'MV01050021', 'MV01050022', 'MV01050023', 'MV01050024'],
                birthdayYear: 2012,
            ),
        ];
    }

    /**
     * @param  array<int, string>  $christianNames
     * @param  array<int, string>  $lastNames
     * @param  array<int, string>  $names
     * @param  array<int, string>  $usernames
     * @return array<int, array{
     *     christian_name: string,
     *     last_name: string,
     *     name: string,
     *     username: string,
     *     birthday: string,
     *     password: string,
     *     role: string
     * }>
     */
    protected function membersForRole(
        string $role,
        array $christianNames,
        array $lastNames,
        array $names,
        array $usernames,
        int $birthdayYear,
    ): array {
        return collect($usernames)
            ->map(function (string $username, int $index) use ($role, $christianNames, $lastNames, $names, $birthdayYear): array {
                $month = str_pad((string) (($index % 12) + 1), 2, '0', STR_PAD_LEFT);
                $day = str_pad((string) (($index % 27) + 1), 2, '0', STR_PAD_LEFT);

                return [
                    'christian_name' => $christianNames[$index],
                    'last_name' => $lastNames[$index],
                    'name' => $names[$index],
                    'username' => $username,
                    'birthday' => "{$birthdayYear}-{$month}-{$day}",
                    'password' => '12345',
                    'role' => $role,
                ];
            })
            ->all();
    }

    /**
     * @param  array{
     *     christian_name: string,
     *     last_name: string,
     *     name: string,
     *     username: string,
     *     birthday: string,
     *     password: string,
     *     role: string,
     *     email?: string
     * }  $member
     */
    protected function upsertUser(array $member): User
    {
        $attributes = collect($member)
            ->except(['role'])
            ->all();

        $user = User::withTrashed()
            ->where('username', $member['username'])
            ->first();

        if ($user === null && filled($member['email'] ?? null)) {
            $user = User::withTrashed()
                ->where('email', $member['email'])
                ->first();
        }

        if ($user !== null) {
            $user->fill($attributes);
            $user->deleted_at = null;
            $user->token = $user->token ?: Str::random(60);
            $user->save();

            return $user;
        }

        $attributes['token'] = Str::random(60);

        return User::create($attributes);
    }

    protected function role(string $roleName): Role
    {
        return Role::findByName($roleName, 'web');
    }
}
