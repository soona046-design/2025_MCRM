<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChannelTreatmentRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channelCategories = \App\Models\ChannelCategory::all();
        $treatmentTypes = \App\Models\TreatmentType::all();

        if ($channelCategories->isEmpty() || $treatmentTypes->isEmpty()) {
            $this->command->warn('채널 카테고리 또는 진료 유형이 없습니다. 먼저 ChannelCategorySeeder와 TreatmentTypeSeeder를 실행하세요.');
            return;
        }

        // 최근 90일간 샘플 데이터 생성 (분석을 위한 충분한 데이터)
        $startDate = now()->subDays(90);
        $endDate = now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // 매일 랜덤하게 10-20개의 레코드 생성 (더 많은 분석 데이터)
            $recordCount = rand(10, 20);

            for ($i = 0; $i < $recordCount; $i++) {
                $channelCategory = $channelCategories->random();
                $treatmentType = $treatmentTypes->random();

                // 중복 체크 (동일 날짜, 채널, 진료유형)
                $existing = \App\Models\ChannelTreatmentRecord::where('record_date', $date->format('Y-m-d'))
                    ->where('channel_category_id', $channelCategory->id)
                    ->where('treatment_type_id', $treatmentType->id)
                    ->first();

                if ($existing) {
                    continue; // 중복되면 스킵
                }

                // 건수는 1-10 사이 랜덤
                $count = rand(1, 10);

                // 매출은 진료 유형에 따라 다르게 설정
                $baseRevenue = match($treatmentType->category) {
                    '보철' => rand(500000, 2000000), // 임플란트, 크라운 등
                    '교정' => rand(300000, 1500000),
                    '보존' => rand(50000, 300000),
                    '미용' => rand(100000, 500000),
                    default => rand(50000, 200000),
                };

                $totalRevenue = $baseRevenue * $count;

                \App\Models\ChannelTreatmentRecord::create([
                    'record_date' => $date->format('Y-m-d'),
                    'channel_category_id' => $channelCategory->id,
                    'treatment_type_id' => $treatmentType->id,
                    'count' => $count,
                    'revenue' => $totalRevenue,
                    'notes' => '샘플 데이터',
                    'input_type' => rand(0, 1) ? 'manual' : 'auto', // 50% 확률로 manual/auto
                    'created_by' => null,
                ]);
            }
        }

        $this->command->info('채널-진료 레코드 샘플 데이터 생성 완료!');
    }
}
