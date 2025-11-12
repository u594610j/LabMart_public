<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // foreach (range(1, 10) as $i) {
        //     Order::create([
        //         'user_id' => rand(1, 3),  // 適当に登録済みuser_idから選ぶ
        //         'ordered_at' => now()->subDays(rand(0, 30)),
        //         'total_price' => 0,  // 詳細を登録してから更新
        //     ]);
        // }
    }
}
