<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $this->baseUrl = config('services.naver_ads.base_url', env('NAVER_ADS_BASE_URL'));
        $this->accessLicense = config('services.naver_ads.access_license', env('NAVER_ADS_ACCESS_LICENSE'));
        $this->secretKey = config('services.naver_ads.secret_key', env('NAVER_ADS_SECRET_KEY'));
        $this->customerId = config('services.naver_ads.customer_id', env('NAVER_ADS_CUSTOMER_ID'));
        $this->mockMode = config('ads.mock', true);
    }

    /**
     * 네이버 광고 API 호출을 위한 서명 생성
     */
    protected function generateSignature($method, $uri, $timestamp)
    {
        $message = $timestamp . '.' . $method . '.' . $uri;
        Log::info('Signature Message', ['message' => $message]); // 디버그 로그 추가
        $hash = hash_hmac('sha256', $message, $this->secretKey, true);
        $signature = base64_encode($hash);
        Log::info('Generated Signature', ['signature' => $signature]); // 디버그 로그 추가
        return $signature;
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
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getAdCosts(string $startDate, string $endDate): array
    {
        Log::info('NaverAdsApiService getAdCosts called', ['startDate' => $startDate, 'endDate' => $endDate]);
        // 네이버 검색광고 API의 통계 보고서 엔드포인트
        $apiUri = '/stats/campaign'; // 실제 API 엔드포인트의 경로 부분 (캠페인 통계로 추정)
        $method = 'GET';
        $timestamp = (string) (int) (microtime(true) * 1000); // 타임스탬프를 정수형으로 변환 후 문자열로 캐스팅

        // 서명 생성에 사용될 URI: baseUrl의 path 부분과 apiUri를 결합
        $pathForSignature = parse_url($this->baseUrl, PHP_URL_PATH) . $apiUri;

        $headers = [
            'X-API-KEY' => $this->accessLicense,
            'X-CUSTOMER-ID' => $this->customerId,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $this->generateSignature($method, $pathForSignature, $timestamp), // pathForSignature 사용
        ];

        try {
            Log::info('NaverAdsApiService making API call', [
                'fullUrl' => $this->baseUrl . $apiUri, // 전체 URL 로깅 추가
                'baseUrl' => $this->baseUrl,
                'uri' => $apiUri,
                'headers' => array_keys($headers), // 보안상 실제 키 값은 제외하고 키만 로깅
                'params' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'timeUnit' => 'DAY',
                    'campaignIds' => [],
                    'fields' => 'impressions,clicks,cost', // Changed from 'period,campaignName,cost'
                    'dataType' => 'CAMPAIGN_REPORT',
                ]
            ]);
            $response = Http::withHeaders($headers)->get($this->baseUrl . $apiUri, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                // 실제 네이버 검색광고 API의 통계 요청 파라미터에 맞게 수정
                // 예시: 'fields' => 'cost,imp,click,ctr', 'timeUnit' => 'DAY'
                'timeUnit' => 'DAY', // 일별 통계 요청
                'campaignIds' => [], // 모든 캠페인 통계를 원한다면 빈 배열 또는 특정 캠페인 ID 지정
                'fields' => 'impressions,clicks,cost', // Changed from 'period,campaignName,cost'
                'dataType' => 'CAMPAIGN_REPORT',
            ]);

            $response->throw(); // 에러 발생 시 예외 던짐

            $data = $response->json();
            Log::info('Naver Ads API raw response', ['data' => $data]);


            // TODO: 실제 네이버 검색광고 API의 통계 보고서 응답 구조에 맞춰 데이터 파싱 로직 구현
            // 현재는 임시 데이터 구조로 가정하여 파싱
            $parsedCosts = [];
            if (is_array($data) && !empty($data)) {
                foreach ($data as $item) {
                    // 실제 응답 필드명에 맞춰 키를 조정합니다.
                    // 예시: item['date'] -> item['period'], item['campaignName'] -> item['name'], item['cost'] -> item['totalCost']
                    $parsedCosts[] = [
                        'date' => $item['period'] ?? null, // 'period' 필드를 날짜로 사용
                        'channel' => '네이버', // 채널명 고정
                        'campaign' => $item['campaignName'] ?? 'unknown', // 'campaignName' 필드를 캠페인명으로 사용
                        'cost' => $item['cost'] ?? 0, // 'cost' 필드를 비용으로 사용
                    ];
                }
            }
            return $parsedCosts;
        } catch (\Exception $e) {
            Log::error('Naver Ads API Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'response' => $e instanceof \Illuminate\Http\Client\RequestException ? $e->response->body() : null,
            ]);
            return [];
        }
    }
}
