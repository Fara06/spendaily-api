<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Expense
            ['name' => 'Makanan',      'icon' => '🍜', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Transportasi', 'icon' => '🚌', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Belanja',      'icon' => '🛍️', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Hiburan',      'icon' => '🎮', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Kesehatan',    'icon' => '💊', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Tagihan',      'icon' => '📋', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Pendidikan',   'icon' => '📚', 'type' => 'expense', 'is_default' => true],
            ['name' => 'Lainnya',      'icon' => '💡', 'type' => 'expense', 'is_default' => true],
            // Income
            ['name' => 'Gaji',        'icon' => '💼', 'type' => 'income', 'is_default' => true],
            ['name' => 'Uang Saku',   'icon' => '👛', 'type' => 'income', 'is_default' => true],
            ['name' => 'Investasi',   'icon' => '📈', 'type' => 'income', 'is_default' => true],
            ['name' => 'Paruh Waktu', 'icon' => '⏰', 'type' => 'income', 'is_default' => true],
            ['name' => 'Bonus',       'icon' => '🎁', 'type' => 'income', 'is_default' => true],
            ['name' => 'Freelance',   'icon' => '💻', 'type' => 'income', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                ...$category,
                'user_id'    => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
