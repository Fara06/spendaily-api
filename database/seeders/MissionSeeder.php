<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mission;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            [
                'title'          => 'Hemat 7 Hari',
                'description'    => 'Tidak belanja selama 7 hari berturut-turut',
                'type'           => 'frequency',
                'target_value'   => 7,
                'duration'       => 7,
                'reward_points'  => 100,
                'color'          => '#4CAF50',
                'is_featured'    => true,
                'is_flash'       => false,
            ],
            [
                'title'          => 'Tabung 500rb',
                'description'    => 'Kumpulkan tabungan sebesar Rp 500.000',
                'type'           => 'amount',
                'target_value'   => 500000,
                'duration'       => 30,
                'reward_points'  => 300,
                'color'          => '#2196F3',
                'is_featured'    => true,
                'is_flash'       => false,
                'estimated_saving' => 500000,
            ],
            [
                'title'          => 'No Belanja Online',
                'description'    => 'Tidak belanja online selama 14 hari',
                'type'           => 'frequency',
                'target_value'   => 14,
                'duration'       => 14,
                'reward_points'  => 200,
                'color'          => '#F44336',
                'is_featured'    => false,
                'is_flash'       => false,
            ],
            [
                'title'          => 'Hemat Kopi',
                'description'    => 'Tidak beli kopi selama 7 hari',
                'type'           => 'frequency',
                'target_value'   => 7,
                'duration'       => 7,
                'reward_points'  => 100,
                'color'          => '#795548',
                'is_featured'    => false,
                'is_flash'       => true,
            ],
            [
                'title'          => 'Nabung 30 Hari',
                'description'    => 'Nabung setiap hari selama 30 hari berturut-turut',
                'type'           => 'frequency',
                'target_value'   => 30,
                'duration'       => 30,
                'reward_points'  => 500,
                'color'          => '#9C27B0',
                'is_featured'    => true,
                'is_flash'       => false,
            ],
        ];

        foreach ($missions as $mission) {
            Mission::firstOrCreate(
                ['title' => $mission['title']],
                $mission
            );
        }
    }
}
