<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'カップ麺・即席食品',
            'レトルト食品',
            'お菓子・スナック',
            '飲料（ソフトドリンク）',
            '飲料（アルコール）',
            'パン・シリアル・バー',
            '缶詰・乾物',
            'その他',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
