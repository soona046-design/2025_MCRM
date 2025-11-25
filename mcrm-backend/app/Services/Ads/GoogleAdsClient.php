<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Google Ads API Client
 * GDN 광고, 유튜브 광고 데이터 수집
 */
class GoogleAdsClient
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $refreshToken;
    protected ?string $customerId;
    protected ?string $developerToken;
    protected bool $mockMode;

    public function __construct()
    {
        $this->clientId = config('ads.google.client_id');
        $this->clientSecret = config('ads.google.client_secret');
        $this->refreshToken = config('ads.google.refresh_token');
        $this->customerId = config('ads.google.customer_id');
        $this->developerToken = config('ads.google.developer_token');
        $this->mockMode = config('ads.mock', true);
    }

    /**
     * 주차별 광고 데이터 조회
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $channelType 'gdn' or 'youtube'
     * @return array
     */
    public function fetchWeekly(Carbon $startDate, Carbon $endDate, string $channelType): array
    {
        if ($this->mockMode) {
            return $this->generateMockData($startDate, $endDate, $channelType);
        }

        try {
            $accessToken = $this->getAccessToken();

            $query = $this->buildQuery($startDate, $endDate, $channelType);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'developer-token' => $this->developerToken,
                'Content-Type' => 'application/json',
            ])->post("https://googleads.googleapis.com/v14/customers/{$this->customerId}/googleAds:searchStream", [
                'query' => $query,
            ]);

            if ($response->failed()) {
                Log::error('Google Ads API 호출 실패', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $this->parseResponse($response->json(), $channelType);
        } catch (\Exception $e) {
            Log::error('Google Ads API 오류', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * Access Token 발급
     */
    protected function getAccessToken(): string
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \Exception('Google OAuth token refresh 실패: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Google Ads Query Language (GAQL) 쿼리 생성
     */
    protected function buildQuery(Carbon $startDate, Carbon $endDate, string $channelType): string
    {
        $start = $startDate->format('Y-m-d');
        $end = $endDate->format('Y-m-d');

        // GDN과 YouTube를 구분하는 필터
        $campaignTypeFilter = match($channelType) {
            'gdn' => "campaign.advertising_channel_type = 'DISPLAY'",
            'youtube' => "campaign.advertising_channel_type = 'VIDEO'",
            default => "campaign.advertising_channel_type IN ('DISPLAY', 'VIDEO')",
        };

        return "
            SELECT
                segments.date,
                metrics.impressions,
                metrics.clicks,
                metrics.conversions,
                metrics.cost_micros
            FROM campaign
            WHERE segments.date BETWEEN '{$start}' AND '{$end}'
              AND {$campaignTypeFilter}
            ORDER BY segments.date
        ";
    }

    /**
     * API 응답 파싱
     */
    protected function parseResponse(array $data, string $channelType): array
    {
        $metrics = [];

        foreach ($data as $result) {
            if (!isset($result['results'])) {
                continue;
            }

            foreach ($result['results'] as $row) {
                $date = $row['segments']['date'] ?? null;
                $impressions = $row['metrics']['impressions'] ?? 0;
                $clicks = $row['metrics']['clicks'] ?? 0;
                $conversions = $row['metrics']['conversions'] ?? 0;
                $costMicros = $row['metrics']['costMicros'] ?? 0;

                // Micros를 KRW로 변환 (1,000,000 micros = 1 currency unit)
                $cost = (int) round($costMicros / 1000000);

                $metrics[] = [
                    'date' => $date,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'conversions' => $conversions,
                    'cost' => $cost,
                    'channel_type' => $channelType,
                ];
            }
        }

        return $metrics;
    }

    /**
     * Mock 데이터 생성
     */
    protected function generateMockData(Carbon $startDate, Carbon $endDate, string $channelType): array
    {
        $metrics = [];
        $current = $startDate->copy();

        // GDN과 YouTube의 기본 성과 차이 반영
        $baseImpressions = match($channelType) {
            'gdn' => rand(80000, 120000),
            'youtube' => rand(50000, 80000),
            default => rand(60000, 100000),
        };

        $baseCtr = match($channelType) {
            'gdn' => 0.8,    // GDN은 낮은 CTR
            'youtube' => 2.5, // 유튜브는 높은 CTR
            default => 1.5,
        };

        while ($current->lte($endDate)) {
            $impressions = $baseImpressions + rand(-10000, 10000);
            $clicks = (int) round($impressions * ($baseCtr / 100) * (rand(80, 120) / 100));
            $conversions = (int) round($clicks * 0.03); // 3% 전환율
            $cost = rand(1500000, 3000000); // 150만~300만원

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
}
