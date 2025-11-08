<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Item::firstOrCreate(
                ['name' => '商品'.$i],
                [
                    'price' => rand(100, 5000),
                    'stock_quantity' => rand(1, 100),
                    'category_id' => rand(1, 8),
                ]
            );
        }
    }
}
