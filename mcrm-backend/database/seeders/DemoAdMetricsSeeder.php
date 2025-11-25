<?php

namespace Database\Seeders;

use App\Models\AdMetric;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoAdMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 8월~10월(15주) 더미 데이터 생성
     */
    public function run(): void
    {
        $this->command->info('🌱 광고 성과 데이터 시딩 시작...');

        // 8월 1일부터 10월 31일까지
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::create(2025, 10, 31);

        $this->seedWeeklyData($startDate, $endDate);
        $this->seedMonthlyData();

        $this->command->info('✅ 광고 성과 데이터 시딩 완료');
    }

    /**
     * 주차별 데이터 생성
     */
    protected function seedWeeklyData(Carbon $start, Carbon $end): void
    {
        $current = $start->copy()->startOfWeek();
        $weekCount = 0;

        while ($current->lte($end)) {
            $weekEnd = $current->copy()->endOfWeek();
            if ($weekEnd->gt($end)) {
                $weekEnd = $end->copy();
            }

            $periodLabel = $current->format('Y') . '-W' . $current->format('W');

            // 네이버 3개 채널
            $this->createMetric('naver', 'keyword', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(150000, 200000),
                'baseCtr' => 3.5,
                'conversionRate' => 0.05,
                'cost' => rand(4000000, 6000000),
            ]);

            $this->createMetric('naver', 'place', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(80000, 120000),
                'baseCtr' => 2.8,
                'conversionRate' => 0.04,
                'cost' => rand(2500000, 4000000),
            ]);

            $this->createMetric('naver', 'powercontent', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(50000, 80000),
                'baseCtr' => 4.2,
                'conversionRate' => 0.06,
                'cost' => rand(3000000, 5000000),
            ]);

            // 구글 2개 채널
            $this->createMetric('google', 'gdn', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(80000, 120000),
                'baseCtr' => 0.8,
                'conversionRate' => 0.02,
                'cost' => rand(2000000, 3500000),
            ]);

            $this->createMetric('google', 'youtube', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(50000, 80000),
                'baseCtr' => 2.5,
                'conversionRate' => 0.03,
                'cost' => rand(2500000, 4000000),
            ]);

            // 메타 1개 채널
            $this->createMetric('meta', 'sns', 'week', $periodLabel, $current, $weekEnd, [
                'impressions' => rand(100000, 150000),
                'baseCtr' => 1.8,
                'conversionRate' => 0.04,
                'cost' => rand(3000000, 5000000),
            ]);

            $current->addWeek();
            $weekCount++;
        }

        $this->command->info("  주차별 데이터: {$weekCount}주 × 6채널 = " . ($weekCount * 6) . "건 생성");
    }

    /**
     * 월별 데이터 생성
     */
    protected function seedMonthlyData(): void
    {
        $months = [
            ['year' => 2025, 'month' => 8],
            ['year' => 2025, 'month' => 9],
            ['year' => 2025, 'month' => 10],
        ];

        foreach ($months as $m) {
            $start = Carbon::create($m['year'], $m['month'], 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $periodLabel = $start->format('Y-m');

            // 네이버 3개 채널
            $this->createMetric('naver', 'keyword', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(600000, 800000),
                'baseCtr' => 3.5,
                'conversionRate' => 0.05,
                'cost' => rand(16000000, 24000000),
            ]);

            $this->createMetric('naver', 'place', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(320000, 480000),
                'baseCtr' => 2.8,
                'conversionRate' => 0.04,
                'cost' => rand(10000000, 16000000),
            ]);

            $this->createMetric('naver', 'powercontent', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(200000, 320000),
                'baseCtr' => 4.2,
                'conversionRate' => 0.06,
                'cost' => rand(12000000, 20000000),
            ]);

            // 구글 2개 채널
            $this->createMetric('google', 'gdn', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(320000, 480000),
                'baseCtr' => 0.8,
                'conversionRate' => 0.02,
                'cost' => rand(8000000, 14000000),
            ]);

            $this->createMetric('google', 'youtube', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(200000, 320000),
                'baseCtr' => 2.5,
                'conversionRate' => 0.03,
                'cost' => rand(10000000, 16000000),
            ]);

            // 메타 1개 채널
            $this->createMetric('meta', 'sns', 'month', $periodLabel, $start, $end, [
                'impressions' => rand(400000, 600000),
                'baseCtr' => 1.8,
                'conversionRate' => 0.04,
                'cost' => rand(12000000, 20000000),
            ]);
        }

        $this->command->info("  월별 데이터: 3개월 × 6채널 = 18건 생성");
    }

    /**
     * 메트릭 생성 헬퍼
     */
    protected function createMetric(
        string $platform,
        string $channelType,
        string $periodType,
        string $periodLabel,
        Carbon $start,
        Carbon $end,
        array $params
    ): void {
        $impressions = $params['impressions'];
        $baseCtr = $params['baseCtr'];
        $conversionRate = $params['conversionRate'];
        $cost = $params['cost'];

        // CTR에 약간의 변동 추가
        $ctr = $baseCtr * (rand(80, 120) / 100);
        $clicks = (int) round($impressions * ($ctr / 100));
        $conversions = (int) round($clicks * $conversionRate);

        AdMetric::create([
            'platform' => $platform,
            'channel_type' => $channelType,
            'period_type' => $periodType,
            'period_label' => $periodLabel,
            'date_start' => $start->format('Y-m-d'),
            'date_end' => $end->format('Y-m-d'),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => $conversions,
            'cost' => $cost,
            'meta_json' => [
                'source' => 'seeder',
                'created_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
