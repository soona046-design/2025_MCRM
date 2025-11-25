<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CostImport;

class CostImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = ['네이버', '구글', '메타'];
        $campaigns = ['캠페인A', '캠페인B', '캠페인C'];

        foreach ($channels as $channel) {
            foreach ($campaigns as $campaign) {
                // 30일치 데이터 생성
                for ($i = 0; $i < 30; $i++) {
                    CostImport::factory()->create([
                        'platform' => $channel, // channel 대신 platform 사용
                        'campaign_code' => $campaign, // campaign 대신 campaign_code 사용
                        'cost' => rand(1000, 500000),
                        'date' => now()->subDays($i)->format('Y-m-d'),
                    ]);
                }
            }
        }
    }
}
