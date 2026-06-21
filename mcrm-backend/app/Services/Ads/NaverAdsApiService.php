<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class NaverAdsApiService
{
    protected $baseUrl;
    protected $accessLicense;
    protected $secretKey;
    protected $customerId;
    protected $mockMode;

    public function __construct()
    {
        $this->baseUrl = config('ads.naver.base_url');
        $this->accessLicense = config('ads.naver.access_license');
        $this->secretKey = config('ads.naver.secret_key');
        $this->customerId = config('ads.naver.customer_id');
        $this->mockMode = config('ads.mock', true);
    }

    /**
     * 네이버 광고 API 호출을 위한 서명 생성
     * 공식 샘플(naver/searchad-apidoc) 규격: "{timestamp}.{method}.{uri}"를 secretKey로 HMAC-SHA256 후 base64
     * uri에는 query string을 포함하지 않음
     */
    protected function generateSignature($method, $uri, $timestamp)
    {
        $message = $timestamp . '.' . $method . '.' . $uri;
        $hash = hash_hmac('sha256', $message, $this->secretKey, true);
        return base64_encode($hash);
    }

    /**
     * 네이버 검색광고 API 인증 헤더 생성
     */
    protected function buildHeaders(string $method, string $uri): array
    {
        $timestamp = (string) (int) (microtime(true) * 1000);

        return [
            'Content-Type' => 'application/json; charset=UTF-8',
            'X-Timestamp' => $timestamp,
            'X-API-KEY' => $this->accessLicense,
            'X-Customer' => $this->customerId,
            'X-Signature' => $this->generateSignature($method, $uri, $timestamp),
        ];
    }

    /**
     * 고객 계정의 캠페인 목록 조회 (GET /ncc/campaigns)
     * /stats는 ids(캠페인 등 객체 ID)를 반드시 필요로 하므로 먼저 캠페인 목록을 가져온다.
     *
     * @return array<int, array{nccCampaignId: string, name: string}>
     */
    protected function getCampaigns(): array
    {
        $uri = '/ncc/campaigns';

        $response = Http::withHeaders($this->buildHeaders('GET', $uri))
            ->get($this->baseUrl . $uri);

        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * 지정 기간의 모든 날짜를 'Y-m-d' 배열로 반환
     */
    protected function enumerateDays(string $startDate, string $endDate): array
    {
        $days = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current->lte($end)) {
            $days[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $days;
    }

    /**
     * 여러 캠페인 × 여러 날짜의 통계를 동시(concurrent) 요청으로 조회 (GET /stats)
     *
     * 주의: 이 계정에서는 timeIncrement(일별 분할 조회) 파라미터가 11001 "지원하지 않는 기능"으로 거부됨
     * (실제 자격증명으로 확인). /stats는 timeRange 전체 구간의 합계 1건만 반환하므로,
     * 일별 데이터가 필요하면 since=until=같은 날짜로 날짜별로 호출해야 함.
     * 캠페인×날짜 조합 수가 많아지면(예: 9개 캠페인 × 81일 ≈ 729건) 순차 호출 시 PHP
     * max_execution_time을 넘겨 서버가 죽으므로, Http::pool로 묶어서 동시 요청한다.
     * 응답 형태: {"data":[{"id":...,"impCnt":...,"clkCnt":...,"salesAmt":...,"ccnt":...}], "compTm":..., "cycleBaseTm":...}
     *
     * @param array<int, array{nccCampaignId: string, name: string}> $campaigns
     * @return array<int, array{platform: string, campaign_code: string, date: string, impressions: int, clicks: int, cost: int}>
     */
    protected function fetchStatsConcurrently(array $campaigns, array $days): array
    {
        $jobs = [];
        foreach ($campaigns as $campaign) {
            $campaignId = $campaign['nccCampaignId'] ?? null;
            if (!$campaignId) {
                continue;
            }
            foreach ($days as $day) {
                $jobs[] = [
                    'campaignId' => $campaignId,
                    'campaignName' => $campaign['name'] ?? $campaignId,
                    'day' => $day,
                ];
            }
        }

        $parsedCosts = [];
        $retryQueue = [];
        $uri = '/stats';
        $chunkSize = 20;
        $first = true;

        // 한 번에 너무 많은 동시 연결을 열지 않도록 청크 단위로 처리 +
        // 네이버 rps 제한(계정당 100 req/s, 실측으로 429 확인됨)에 걸리지 않도록 청크 사이에 간격을 둠
        foreach (array_chunk($jobs, $chunkSize) as $chunk) {
            if (!$first) {
                usleep(300_000); // 0.3초 — chunkSize=20 기준 약 65 req/s로 100 req/s 한도 아래 유지
            }
            $first = false;

            [$results, $retries] = $this->fetchStatsChunk($chunk, $uri);
            $parsedCosts = array_merge($parsedCosts, $results);
            $retryQueue = array_merge($retryQueue, $retries);
        }

        // 429 등으로 실패한 요청은 한 번 더 여유를 두고 재시도
        if (!empty($retryQueue)) {
            usleep(500_000);
            foreach (array_chunk($retryQueue, $chunkSize) as $chunk) {
                [$results, $stillFailed] = $this->fetchStatsChunk($chunk, $uri);
                $parsedCosts = array_merge($parsedCosts, $results);
                foreach ($stillFailed as $job) {
                    Log::error('Naver Ads API 통계 조회 재시도도 실패', $job);
                }
                usleep(300_000);
            }
        }

        return $parsedCosts;
    }

    /**
     * (campaignId, day) 작업 묶음 하나를 동시 요청으로 처리.
     *
     * @return array{0: array<int, array>, 1: array<int, array{campaignId: string, campaignName: string, day: string}>}
     *         [성공 결과 목록, 실패해서 재시도가 필요한 job 목록]
     */
    protected function fetchStatsChunk(array $chunk, string $uri): array
    {
        $responses = Http::pool(function ($pool) use ($chunk, $uri) {
            foreach ($chunk as $i => $job) {
                $pool->as($i)
                    ->withHeaders($this->buildHeaders('GET', $uri))
                    ->get($this->baseUrl . $uri, [
                        'ids' => $job['campaignId'],
                        'fields' => json_encode(['impCnt', 'clkCnt', 'salesAmt', 'ccnt']),
                        'timeRange' => json_encode(['since' => $job['day'], 'until' => $job['day']]),
                    ]);
            }
        });

        $results = [];
        $failed = [];

        foreach ($chunk as $i => $job) {
            $response = $responses[$i] ?? null;

            if (!($response instanceof \Illuminate\Http\Client\Response) || $response->failed()) {
                $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : null;
                Log::warning('Naver Ads API 통계 조회 실패(재시도 예정)', [
                    'campaignId' => $job['campaignId'],
                    'day' => $job['day'],
                    'status' => $status,
                    'body' => $response instanceof \Illuminate\Http\Client\Response ? $response->body() : ($response instanceof \Throwable ? $response->getMessage() : null),
                ]);
                $failed[] = $job;
                continue;
            }

            $row = $response->json('data.0');
            if ($row) {
                $results[] = [
                    'platform' => 'naver', // ad_metrics/AdWebhookController와 동일한 영문 코드 컨벤션 (CostImport::platform)
                    'campaign_code' => $job['campaignName'],
                    'date' => $job['day'],
                    'impressions' => $row['impCnt'] ?? 0,
                    'clicks' => $row['clkCnt'] ?? 0,
                    'cost' => $row['salesAmt'] ?? 0,
                ];
            }
        }

        return [$results, $failed];
    }

    /**
     * 주차별 광고 데이터 조회 (새 통합 인터페이스)
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $channelType 'keyword' | 'place' | 'powercontent'
     * @return array
     */
    public function fetchWeekly(Carbon $startDate, Carbon $endDate, string $channelType): array
    {
        if ($this->mockMode) {
            return $this->generateMockData($startDate, $endDate, $channelType);
        }

        // 실제 API 호출은 기존 getAdCosts 사용
        $data = $this->getAdCosts($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        // channel_type 필드 추가
        return array_map(function ($item) use ($channelType) {
            return array_merge($item, ['channel_type' => $channelType]);
        }, $data);
    }

    /**
     * Mock 데이터 생성
     */
    protected function generateMockData(Carbon $startDate, Carbon $endDate, string $channelType): array
    {
        $metrics = [];
        $current = $startDate->copy();

        // 채널별 성과 차이 반영
        [$baseImpressions, $baseCtr] = match($channelType) {
            'keyword' => [rand(150000, 200000), 3.5], // 키워드: 높은 노출, 높은 CTR
            'place' => [rand(80000, 120000), 2.8],    // 플레이스: 중간 노출, 중간 CTR
            'powercontent' => [rand(50000, 80000), 4.2], // 파워컨텐츠: 낮은 노출, 높은 CTR
            default => [rand(100000, 150000), 3.0],
        };

        while ($current->lte($endDate)) {
            $impressions = $baseImpressions + rand(-20000, 20000);
            $clicks = (int) round($impressions * ($baseCtr / 100) * (rand(80, 120) / 100));
            $conversions = (int) round($clicks * 0.05); // 5% 전환율
            $cost = rand(2500000, 5000000); // 250만~500만원

            $metrics[] = [
                'date' => $current->format('Y-m-d'),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'cost' => $cost,
                'channel_type' => $channelType,
            ];

            $current->addDay();
        }

        return $metrics;
    }

    /**
     * 지정된 기간 동안 네이버 광고 비용 데이터를 가져옵니다.
     *
     * - mock 모드면 실제 API를 전혀 호출하지 않음 (이전엔 호출부가 mockMode를 무시해서
     *   페이지 새로고침마다 실패하는 API를 계속 호출하던 버그가 있었음)
     * - 캠페인×날짜 조합이 많을 수 있어 결과를 짧게 캐싱해서 같은 기간을 반복 조회할 때
     *   매번 네이버 서버를 다시 두드리지 않도록 함
     *
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getAdCosts(string $startDate, string $endDate): array
    {
        if ($this->mockMode) {
            Log::info('NaverAdsApiService getAdCosts: mock 모드라 실제 API 호출을 생략함');
            return [];
        }

        $cacheKey = "naver_ad_costs:{$this->customerId}:{$startDate}:{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($startDate, $endDate) {
            return $this->fetchAdCostsFromApi($startDate, $endDate);
        });
    }

    /**
     * 캠페인 목록 조회 후 캠페인×날짜 조합을 동시 요청으로 가져와 CostImport 저장용 형태로 변환
     */
    protected function fetchAdCostsFromApi(string $startDate, string $endDate): array
    {
        Log::info('NaverAdsApiService 실제 API 호출 시작', ['startDate' => $startDate, 'endDate' => $endDate]);

        // 캠페인×날짜 조합이 많을 경우(예: 6개월 조회) 동시 처리로도 시간이 걸릴 수 있어
        // PHP 기본 30초 제한으로 죽지 않도록 여유를 둠 (CLI/서버 설정상 무제한(0)이면 그대로 둠)
        if ((int) ini_get('max_execution_time') !== 0) {
            set_time_limit(120);
        }

        try {
            $campaigns = $this->getCampaigns();
        } catch (\Exception $e) {
            Log::error('Naver Ads API 캠페인 목록 조회 실패', [
                'message' => $e->getMessage(),
                'response' => $e instanceof \Illuminate\Http\Client\RequestException ? $e->response->body() : null,
            ]);
            return [];
        }

        $days = $this->enumerateDays($startDate, $endDate);
        $parsedCosts = $this->fetchStatsConcurrently($campaigns, $days);

        Log::info('Naver Ads API costs parsed', [
            'campaigns' => count($campaigns),
            'days' => count($days),
            'count' => count($parsedCosts),
        ]);

        return $parsedCosts;
    }
}
