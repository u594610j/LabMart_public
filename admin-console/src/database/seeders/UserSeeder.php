<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'test_user',
                'grade' => 'B4',
                'card_id' => '0000000000000000',
            ],
            [
                'name' => 'user1',
                'grade' => 'M1',
                'card_id' => 'ABCDEF1234567890',
            ],
            [
                'name' => 'user2',
                'grade' => 'B4',
                'card_id' => '1234567890ABCDEF',
            ],
            [
                'name' => 'user3',
                'grade' => 'M2',
                'card_id' => 'FEDCBA0987654321',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['card_id' => $user['card_id']], // カードIDで検索
                [
                    'name' => $user['name'],
                    'grade' => $user['grade'],
                    'total_amount' => 0,
                ]
            );
        }
    }
}
