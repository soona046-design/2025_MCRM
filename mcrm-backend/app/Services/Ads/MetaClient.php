<?php

namespace App\Services\Ads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Meta (Facebook/Instagram) Ads API Client
 * SNS 광고 데이터 수집
 */
class MetaClient
{
    protected ?string $accessToken;
    protected ?string $adAccountId;
    protected bool $mockMode;
    protected string $apiVersion = 'v18.0';

    public function __construct()
    {
        $this->accessToken = config('ads.meta.access_token');
        $this->adAccountId = config('ads.meta.ad_account_id');
        $this->mockMode = config('ads.mock', true);
    }

    /**
     * 주차별 광고 데이터 조회
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function fetchWeekly(Carbon $startDate, Carbon $endDate): array
    {
        if ($this->mockMode) {
            return $this->generateMockData($startDate, $endDate);
        }

        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/act_{$this->adAccountId}/insights";

            $response = Http::get($url, [
                'access_token' => $this->accessToken,
                'time_range' => json_encode([
                    'since' => $startDate->format('Y-m-d'),
                    'until' => $endDate->format('Y-m-d'),
                ]),
                'time_increment' => 1, // 일별 데이터
                'level' => 'account',
                'fields' => implode(',', [
                    'impressions',
                    'clicks',
                    'actions', // 전환 데이터
                    'spend',
                    'date_start',
                    'date_stop',
                ]),
            ]);

            if ($response->failed()) {
                Log::error('Meta Ads API 호출 실패', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $this->parseResponse($response->json());
        } catch (\Exception $e) {
            Log::error('Meta Ads API 오류', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * API 응답 파싱
     */
    protected function parseResponse(array $data): array
    {
        $metrics = [];

        if (!isset($data['data'])) {
            return $metrics;
        }

        foreach ($data['data'] as $row) {
            $date = $row['date_start'] ?? null;
            $impressions = (int) ($row['impressions'] ?? 0);
            $clicks = (int) ($row['clicks'] ?? 0);
            $spend = (float) ($row['spend'] ?? 0);

            // 전환 수 추출 (actions 배열에서 'lead' 또는 'complete_registration' 액션 찾기)
            $conversions = 0;
            if (isset($row['actions'])) {
                foreach ($row['actions'] as $action) {
                    if (in_array($action['action_type'], ['lead', 'complete_registration', 'purchase'])) {
                        $conversions += (int) ($action['value'] ?? 0);
                    }
                }
            }

            // Spend는 USD로 반환되므로 KRW로 변환 (환율 1300원 가정)
            $cost = (int) round($spend * 1300);

            $metrics[] = [
                'date' => $date,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'cost' => $cost,
                'channel_type' => 'sns',
            ];
        }

        return $metrics;
    }

    /**
     * Mock 데이터 생성
     */
    protected function generateMockData(Carbon $startDate, Carbon $endDate): array
    {
        $metrics = [];
        $current = $startDate->copy();

        // SNS 광고 특성: 높은 노출, 중간 CTR
        $baseImpressions = rand(100000, 150000);
        $baseCtr = 1.8; // 1.8% CTR

        while ($current->lte($endDate)) {
            $impressions = $baseImpressions + rand(-15000, 15000);
            $clicks = (int) round($impressions * ($baseCtr / 100) * (rand(80, 120) / 100));
            $conversions = (int) round($clicks * 0.04); // 4% 전환율
            $cost = rand(2000000, 4000000); // 200만~400만원

            $metrics[] = [
                'date' => $current->format('Y-m-d'),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'cost' => $cost,
                'channel_type' => 'sns',
            ];

            $current->addDay();
        }

        return $metrics;
    }

    /**
     * 장기 액세스 토큰 갱신
     * (단기 토큰을 장기 토큰으로 교환)
     */
    public function refreshLongLivedToken(string $shortLivedToken): ?string
    {
        try {
            $appId = config('ads.meta.app_id');
            $appSecret = config('ads.meta.app_secret');

            $response = Http::get("https://graph.facebook.com/{$this->apiVersion}/oauth/access_token", [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortLivedToken,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Meta 장기 토큰 갱신 실패', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Meta 토큰 갱신 오류', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
