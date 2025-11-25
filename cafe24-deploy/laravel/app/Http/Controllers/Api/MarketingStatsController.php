<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * MarketingStatsController
 * 광고 성과 데이터 조회 API
 */
class MarketingStatsController extends Controller
{
    /**
     * 주차별 광고 성과 데이터 조회
     *
     * GET /api/marketing-stats
     * Query Params:
     *   - period: 조회 기간 (YYYY-MM, YYYY-Www)
     *   - platform: 플랫폼 필터 (naver|google|meta)
     *   - channel_type: 채널 유형 필터
     *   - period_type: week|month
     *   - from: 시작 날짜 (YYYY-MM-DD)
     *   - to: 종료 날짜 (YYYY-MM-DD)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AdMetric::query();

        // 플랫폼 필터
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }

        // 채널 유형 필터
        if ($request->has('channel_type')) {
            $query->where('channel_type', $request->channel_type);
        }

        // 기간 유형 필터
        if ($request->has('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        // 특정 기간 레이블
        if ($request->has('period')) {
            $query->where('period_label', $request->period);
        }

        // 날짜 범위 필터
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date_start', [
                $request->from,
                $request->to,
            ]);
        }

        // 정렬
        $metrics = $query->orderBy('date_start', 'asc')
            ->orderBy('platform')
            ->orderBy('channel_type')
            ->get();

        // 플랫폼별로 그룹화
        $grouped = $metrics->groupBy('platform')->map(function ($platformData, $platform) {
            return [
                'platform' => $platform,
                'platform_label' => $platformData->first()->platform_label,
                'channels' => $platformData->groupBy('channel_type')->map(function ($channelData, $channelType) {
                    return [
                        'channel_type' => $channelType,
                        'channel_label' => $channelData->first()->channel_type_label,
                        'data' => $channelData->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'period_label' => $item->period_label,
                                'period_type' => $item->period_type,
                                'date_start' => $item->date_start->format('Y-m-d'),
                                'date_end' => $item->date_end->format('Y-m-d'),
                                'impressions' => $item->impressions,
                                'clicks' => $item->clicks,
                                'ctr' => $item->ctr,
                                'conversions' => $item->conversions,
                                'cost' => $item->cost,
                                'cpl' => $item->cpl,
                                'cpa' => $item->cpa,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'data' => $grouped,
            'meta' => [
                'total' => $metrics->count(),
                'filters' => $request->only(['platform', 'channel_type', 'period_type', 'period', 'from', 'to']),
            ],
        ]);
    }

    /**
     * 특정 플랫폼의 데이터 조회
     *
     * GET /api/marketing-stats/{platform}
     */
    public function show(string $platform, Request $request): JsonResponse
    {
        $query = AdMetric::where('platform', $platform);

        // 채널 유형 필터
        if ($request->has('channel_type')) {
            $query->where('channel_type', $request->channel_type);
        }

        // 기간 유형 필터
        if ($request->has('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        // 날짜 범위 필터
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date_start', [
                $request->from,
                $request->to,
            ]);
        }

        $metrics = $query->orderBy('date_start', 'asc')
            ->orderBy('channel_type')
            ->get();

        if ($metrics->isEmpty()) {
            return response()->json([
                'message' => '데이터가 없습니다.',
                'data' => [],
            ], 404);
        }

        // 채널별로 그룹화
        $channelsData = $metrics->groupBy('channel_type')->map(function ($channelData, $channelType) {
            return [
                'channel_type' => $channelType,
                'channel_label' => $channelData->first()->channel_type_label,
                'summary' => [
                    'total_impressions' => $channelData->sum('impressions'),
                    'total_clicks' => $channelData->sum('clicks'),
                    'total_conversions' => $channelData->sum('conversions'),
                    'total_cost' => $channelData->sum('cost'),
                    'avg_ctr' => $channelData->avg('ctr'),
                    'avg_cpl' => $channelData->avg('cpl'),
                    'avg_cpa' => $channelData->avg('cpa'),
                ],
                'periods' => $channelData->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'period_label' => $item->period_label,
                        'period_type' => $item->period_type,
                        'date_start' => $item->date_start->format('Y-m-d'),
                        'date_end' => $item->date_end->format('Y-m-d'),
                        'impressions' => $item->impressions,
                        'clicks' => $item->clicks,
                        'ctr' => $item->ctr,
                        'conversions' => $item->conversions,
                        'cost' => $item->cost,
                        'cpl' => $item->cpl,
                        'cpa' => $item->cpa,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'platform' => $platform,
            'platform_label' => $metrics->first()->platform_label,
            'data' => $channelsData,
            'meta' => [
                'total_periods' => $metrics->count(),
                'filters' => $request->only(['channel_type', 'period_type', 'from', 'to']),
            ],
        ]);
    }

    /**
     * 요약 통계 조회
     *
     * GET /api/marketing-stats/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $query = AdMetric::query();

        // 날짜 범위 필터
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date_start', [
                $request->from,
                $request->to,
            ]);
        } else {
            // 기본: 최근 4주
            $query->where('date_start', '>=', now()->subWeeks(4));
        }

        $metrics = $query->get();

        if ($metrics->isEmpty()) {
            return response()->json([
                'message' => '데이터가 없습니다.',
                'summary' => null,
            ]);
        }

        $summary = [
            'total_impressions' => $metrics->sum('impressions'),
            'total_clicks' => $metrics->sum('clicks'),
            'total_conversions' => $metrics->sum('conversions'),
            'total_cost' => $metrics->sum('cost'),
            'avg_ctr' => round($metrics->avg('ctr'), 3),
            'avg_cpl' => round($metrics->avg('cpl'), 0),
            'avg_cpa' => round($metrics->avg('cpa'), 0),
            'by_platform' => $metrics->groupBy('platform')->map(function ($data, $platform) {
                return [
                    'platform' => $platform,
                    'total_cost' => $data->sum('cost'),
                    'total_conversions' => $data->sum('conversions'),
                    'avg_cpl' => round($data->avg('cpl'), 0),
                ];
            })->values(),
        ];

        return response()->json([
            'summary' => $summary,
            'period' => [
                'from' => $metrics->min('date_start'),
                'to' => $metrics->max('date_end'),
            ],
        ]);
    }
}
