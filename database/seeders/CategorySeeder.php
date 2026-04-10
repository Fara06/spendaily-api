<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    
    {
        $defaults = [
            ['name' => 'Makanan', 'icon' => '🍜', 'type' => 'expense'],
            ['name' => 'Transportasi', 'icon' => '🚗', 'type' => 'expense'],
            ['name' => 'Belanja', 'icon' => '🛍️', 'type' => 'expense'],
            ['name' => 'Hiburan', 'icon' => '🎮', 'type' => 'expense'],
            ['name' => 'Kesehatan', 'icon' => '💊', 'type' => 'expense'],
            ['name' => 'Tagihan', 'icon' => '📋', 'type' => 'expense'],
            ['name' => 'Pendidikan', 'icon' => '📚', 'type' => 'expense'],
            ['name' => 'Lainnya', 'icon' => '💡', 'type' => 'expense'],
            ['name' => 'Gaji', 'icon' => '💼', 'type' => 'income'],
            ['name' => 'Uang Saku', 'icon' => '🎒', 'type' => 'income'],
            ['name' => 'Investasi', 'icon' => '📈', 'type' => 'income'],
            ['name' => 'Paruh Waktu', 'icon' => '⏰', 'type' => 'income'],
            ['name' => 'Bonus', 'icon' => '🎁', 'type' => 'income'],
            ['name' => 'Freelance', 'icon' => '💻', 'type' => 'income'],
        ];

        foreach ($defaults as $cat) {
            Category::create([...$cat, 'is_default' => true, 'user_id' => null]);
        }
    }
}
