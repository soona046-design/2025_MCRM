<?php

namespace App\Console\Commands;

use App\Models\AdMetric;
use App\Services\Ads\NaverAdsApiService;
use App\Services\Ads\GoogleAdsClient;
use App\Services\Ads\MetaClient;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchAdStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:fetch
                            {--from= : 시작 날짜 (YYYY-MM-DD)}
                            {--to= : 종료 날짜 (YYYY-MM-DD)}
                            {--granularity=week : 집계 단위 (week|month)}
                            {--platform= : 특정 플랫폼만 수집 (naver|google|meta)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '광고 플랫폼(네이버/구글/메타)에서 성과 데이터를 수집하여 ad_metrics 테이블에 저장';

    protected NaverAdsApiService $naverClient;
    protected GoogleAdsClient $googleClient;
    protected MetaClient $metaClient;

    public function __construct(
        NaverAdsApiService $naverClient,
        GoogleAdsClient $googleClient,
        MetaClient $metaClient
    ) {
        parent::__construct();
        $this->naverClient = $naverClient;
        $this->googleClient = $googleClient;
        $this->metaClient = $metaClient;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = $this->option('from') ?? now()->subWeek()->format('Y-m-d');
        $to = $this->option('to') ?? now()->format('Y-m-d');
        $granularity = $this->option('granularity');
        $platformFilter = $this->option('platform');

        $this->info("📊 광고 데이터 수집 시작");
        $this->info("기간: {$from} ~ {$to}");
        $this->info("집계 단위: {$granularity}");

        try {
            $startDate = Carbon::parse($from);
            $endDate = Carbon::parse($to);

            // 기간을 주차 또는 월별로 분할
            $periods = $this->splitIntoPeriods($startDate, $endDate, $granularity);

            $this->info("총 {$periods->count()}개 기간 처리 예정");

            $successCount = 0;
            $failureCount = 0;

            foreach ($periods as $period) {
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
                $periodLabel = $period['label'];

                $this->line("→ {$periodLabel} 처리 중... ({$periodStart->format('Y-m-d')} ~ {$periodEnd->format('Y-m-d')})");

                // 플랫폼별 데이터 수집
                $platforms = $platformFilter ? [$platformFilter] : ['naver', 'google', 'meta'];

                foreach ($platforms as $platform) {
                    try {
                        $result = $this->fetchPlatformData($platform, $periodStart, $periodEnd, $periodLabel, $granularity);
                        if ($result) {
                            $successCount++;
                            $this->info("  ✓ {$platform} 수집 완료");
                        } else {
                            $failureCount++;
                            $this->warn("  ⚠ {$platform} 데이터 없음");
                        }
                    } catch (\Exception $e) {
                        $failureCount++;
                        $this->error("  ✗ {$platform} 오류: " . $e->getMessage());
                        Log::error("FetchAdStats - {$platform} 오류", [
                            'period' => $periodLabel,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }

            $this->info("\n✅ 데이터 수집 완료");
            $this->info("성공: {$successCount} | 실패: {$failureCount}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ 오류 발생: " . $e->getMessage());
            Log::error('FetchAdStats 실행 오류', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * 기간을 주차 또는 월별로 분할
     */
    protected function splitIntoPeriods(Carbon $start, Carbon $end, string $granularity): \Illuminate\Support\Collection
    {
        $periods = collect();

        if ($granularity === 'week') {
            // 주차별 분할 (월요일 시작)
            $current = $start->copy()->startOfWeek();

            while ($current->lte($end)) {
                $periodEnd = $current->copy()->endOfWeek();
                if ($periodEnd->gt($end)) {
                    $periodEnd = $end->copy();
                }

                // ISO 8601 주차 형식: YYYY-Www
                $label = $current->format('Y') . '-W' . $current->format('W');

                $periods->push([
                    'start' => $current->copy(),
                    'end' => $periodEnd,
                    'label' => $label,
                ]);

                $current->addWeek();
            }
        } else {
            // 월별 분할
            $current = $start->copy()->startOfMonth();

            while ($current->lte($end)) {
                $periodEnd = $current->copy()->endOfMonth();
                if ($periodEnd->gt($end)) {
                    $periodEnd = $end->copy();
                }

                $label = $current->format('Y-m');

                $periods->push([
                    'start' => $current->copy(),
                    'end' => $periodEnd,
                    'label' => $label,
                ]);

                $current->addMonth();
            }
        }

        return $periods;
    }

    /**
     * 플랫폼별 데이터 수집 및 저장
     */
    protected function fetchPlatformData(
        string $platform,
        Carbon $start,
        Carbon $end,
        string $periodLabel,
        string $periodType
    ): bool {
        $channelTypes = match($platform) {
            'naver' => ['keyword', 'place', 'powercontent'],
            'google' => ['gdn', 'youtube'],
            'meta' => ['sns'],
            default => [],
        };

        $hasData = false;

        foreach ($channelTypes as $channelType) {
            $metrics = $this->fetchChannelMetrics($platform, $channelType, $start, $end);

            if (empty($metrics)) {
                continue;
            }

            // 기간 동안의 데이터를 합산
            $aggregated = $this->aggregateMetrics($metrics);

            // DB에 upsert
            AdMetric::updateOrCreate(
                [
                    'platform' => $platform,
                    'channel_type' => $channelType,
                    'period_type' => $periodType,
                    'period_label' => $periodLabel,
                ],
                [
                    'date_start' => $start->format('Y-m-d'),
                    'date_end' => $end->format('Y-m-d'),
                    'impressions' => $aggregated['impressions'],
                    'clicks' => $aggregated['clicks'],
                    'conversions' => $aggregated['conversions'],
                    'cost' => $aggregated['cost'],
                    'meta_json' => [
                        'source' => config('ads.mock') ? 'mock' : 'api',
                        'fetched_at' => now()->toIso8601String(),
                        'raw_data' => $metrics,
                    ],
                ]
            );

            $hasData = true;
        }

        return $hasData;
    }

    /**
     * 채널별 메트릭 수집
     */
    protected function fetchChannelMetrics(string $platform, string $channelType, Carbon $start, Carbon $end): array
    {
        return match($platform) {
            'naver' => $this->naverClient->fetchWeekly($start, $end, $channelType),
            'google' => $this->googleClient->fetchWeekly($start, $end, $channelType),
            'meta' => $this->metaClient->fetchWeekly($start, $end),
            default => [],
        };
    }

    /**
     * 일별 데이터를 기간별로 합산
     */
    protected function aggregateMetrics(array $metrics): array
    {
        return [
            'impressions' => array_sum(array_column($metrics, 'impressions')),
            'clicks' => array_sum(array_column($metrics, 'clicks')),
            'conversions' => array_sum(array_column($metrics, 'conversions')),
            'cost' => array_sum(array_column($metrics, 'cost')),
        ];
    }
}
