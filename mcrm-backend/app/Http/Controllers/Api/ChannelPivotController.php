<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Lead;
use App\Models\Appointment;
use App\Models\CostImport;
use App\Models\Visit;
use App\Services\Ads\NaverAdsApiService;

class ChannelPivotController extends Controller
{
    /**
     * 채널(visits.utm_source, 한글/영문 표기 혼재) → 광고 플랫폼 코드(ad_metrics.platform,
     * cost_imports.platform과 동일한 영문 코드 컨벤션) 매핑.
     * utm_source는 자유 입력값이라 같은 채널이 '네이버'/'naver'처럼 여러 표기로 섞여 들어옴.
     */
    private const PLATFORM_MAPPING = [
        '네이버' => 'naver',
        'naver' => 'naver',
        '구글' => 'google',
        'google' => 'google',
        'Google Ads' => 'google',
        '메타' => 'meta',
        'meta' => 'meta',
        'facebook' => 'meta',
        'Facebook Ads' => 'meta',
        'instagram' => 'meta',
    ];

    /**
     * cost_imports.platform 코드 → 화면 표시용 채널명.
     * 해당 기간에 리드가 없어 utm_source로 채널명을 알 수 없는 플랫폼의 광고비 행에 사용.
     */
    private const PLATFORM_DISPLAY_NAMES = [
        'naver' => '네이버',
        'google' => '구글',
        'meta' => '메타',
    ];

    protected $naverAdsApiService;

    public function __construct(NaverAdsApiService $naverAdsApiService)
    {
        $this->naverAdsApiService = $naverAdsApiService;
    }

    public function index(Request $request)
    {
        Log::info('ChannelPivotController@index started.');
        Log::info('All request parameters', $request->all());
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $clinicId = $request->input('clinicId');

        Log::info('Request parameters', ['startDate' => $startDate, 'endDate' => $endDate, 'clinicId' => $clinicId]);

        // Fetch Naver Ads data and store/update in CostImport
        if ($startDate && $endDate) {
            Log::info('Attempting to fetch Naver Ads data.', ['startDate' => $startDate, 'endDate' => $endDate]);
            try {
                // 기존 데이터 삭제 (Facebook) - 네이버 동기화는 syncCostImports()가 자체적으로 delete+create 처리
                CostImport::where('platform', 'Facebook')->whereBetween('date', [$startDate, $endDate])->delete();

                $naverCosts = $this->naverAdsApiService->syncCostImports($startDate, $endDate);
                Log::info('Naver Ads costs stored/updated in CostImport.', ['count' => count($naverCosts)]);

            } catch (\Exception $e) {
                Log::error('Error fetching/storing Naver Ads costs: ' . $e->getMessage(), ['exception' => $e]);
                // 예외 발생 시 빈 배열로 처리하거나, 에러를 프론트엔드로 전달하는 방식 선택
                // 현재는 오류를 로그로 남기고 계속 진행 (데이터는 없을 수 있음)
            }
        }

        // Prepare base query for leads with visit information and category mapping
        // leftJoin 사용: source_visit_id가 없는 리드(채널 미연결)도 "채널 미확인"으로 집계에 포함시킴
        $leadsQuery = Lead::query()
            ->leftJoin('visits', 'leads.source_visit_id', '=', 'visits.visit_id')
            ->leftJoin('channel_categories as cc', 'visits.channel_category', '=', 'cc.code')
            ->select(
                'leads.lead_id',
                'leads.status',
                DB::raw("COALESCE(visits.utm_source, '채널 미확인') as utm_source"),
                'visits.utm_campaign',
                'visits.channel_category as category_code',
                'cc.name as category_name',
                'cc.color as category_color'
            );

        if ($clinicId) {
            // $leadsQuery->where('leads.clinic_id', $clinicId); // Leads 모델에 clinic_id가 없으므로 제거
        }

        // Apply date filters to leads
        if ($startDate) {
            $leadsQuery->whereDate('leads.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $leadsQuery->whereDate('leads.created_at', '<=', $endDate);
        }
        
        $leads = $leadsQuery->get();
        Log::info('Leads fetched', ['count' => $leads->count(), 'first_lead' => $leads->first()]);

        // pending/rejected 리드도 "마지막으로 도달했던 단계"를 퍼널 집계에 포함시키기 위한 유효 상태 맵
        // (예: 예약 단계까지 갔다가 rejected된 리드는 '예약' 집계에서 그대로 빠지던 문제를 보정)
        $effectiveStatusByLeadId = $this->resolveEffectiveStatuses($leads);

        // 약속 데이터 (leads->visits는 leftJoin: 채널 미연결 리드의 예약도 누락 없이 포함)
        $appointmentsQuery = Appointment::select(
            'appointments.*',
            DB::raw("COALESCE(visits.utm_source, '채널 미확인') as utm_source"),
            'visits.utm_campaign'
        )
            ->join('leads', 'appointments.lead_id', '=', 'leads.lead_id')
            ->leftJoin('visits', 'leads.source_visit_id', '=', 'visits.visit_id');

        // Apply date filters to appointments
        if ($startDate) {
            $appointmentsQuery->whereDate('appointments.slot_at', '>=', $startDate);
        }
        if ($endDate) {
            $appointmentsQuery->whereDate('appointments.slot_at', '<=', $endDate);
        }

        $appointments = $appointmentsQuery->get();
        Log::info('Appointments fetched', ['count' => $appointments->count(), 'first_appointment' => $appointments->first()]);

        // Prepare base query for cost imports
        $costImportsQuery = CostImport::query();

        // Apply date filters to cost imports
        if ($startDate) {
            $costImportsQuery->whereDate('cost_imports.date', '>=', $startDate);
        }
        if ($endDate) {
            $costImportsQuery->whereDate('cost_imports.date', '<=', $endDate);
        }
        // Apply clinic_id filter to cost imports if it's relevant (e.g., if CostImport has clinic_id)
        // Currently, assuming CostImport does not directly have clinic_id, but it can be inferred via channel/campaign.
        // If CostImport model has a direct clinic_id, uncomment and use below:
        // if ($clinicId) {
        //     $costImportsQuery->where('clinic_id', $clinicId);
        // }

        $costImports = $costImportsQuery->get();
        Log::info('CostImports fetched', ['count' => $costImports->count(), 'first_cost_import' => $costImports->first()]);

        Log::info('Leads utm_sources before grouping', $leads->pluck('utm_source')->toArray());

        // Aggregate channel performance data (채널별 집계)
        $channelPerformanceData = $leads->groupBy('utm_source')->map(function ($leadsByChannel, $channel) use ($appointments, $costImports, $leads, $effectiveStatusByLeadId) {
            $totalLeads = $leadsByChannel->count();

            // 카테고리 정보 가져오기 (첫 번째 lead에서)
            $firstLead = $leadsByChannel->first();
            $categoryCode = $firstLead->category_code ?? null;
            $categoryName = $firstLead->category_name ?? '알 수 없음';
            $categoryColor = $firstLead->category_color ?? '#9E9E9E';

            // 누적 상태 기반 카운팅 (퍼널: 문의→상담→예약→계약)
            // new: leads (전환수) - 모든 리드가 new 이상
            // pending/rejected 리드는 effectiveStatus(마지막 도달 단계)로 환산해서 집계
            // contacted 이상: tickets_count (상담수)
            $totalTickets = $leadsByChannel->filter(function($lead) use ($effectiveStatusByLeadId) {
                $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                return in_array($status, ['contacted', 'scheduled', 'converted']);
            })->count();

            // scheduled 이상: appointments_count (예약수)
            $totalAppointments = $leadsByChannel->filter(function($lead) use ($effectiveStatusByLeadId) {
                $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                return in_array($status, ['scheduled', 'converted']);
            })->count();

            // converted: contracts_count (계약 완료)
            $totalContracts = $leadsByChannel->filter(function($lead) {
                return $lead->status === 'converted';
            })->count();

            // converted: revenue 집계 (계약 완료된 리드의 수익)
            $totalRevenue = 0;
            $leadIds = $leadsByChannel->pluck('lead_id')->toArray();
            $completedLeads = $leadsByChannel->filter(function($lead) {
                return $lead->status === 'converted';
            });
            foreach ($completedLeads as $lead) {
                $appointment = \App\Models\Appointment::where('lead_id', $lead->lead_id)->first();
                if ($appointment) {
                    $totalRevenue += $appointment->total_revenue ?? 0;
                }
            }

            // 채널(visits.utm_source, 한글/영문 표기가 혼재) → 광고 플랫폼 코드(ad_metrics/cost_imports와 동일한 영문 코드) 매핑
            $platformMapping = self::PLATFORM_MAPPING;
            // cost_imports.platform은 항상 영문 코드로 저장되므로, 매핑이 없으면 $channel 그대로 시도(수동 입력 비용 등 대비)
            $totalCost = $costImports->where('platform', $platformMapping[$channel] ?? $channel)->sum('cost');

            // 노출/클릭은 cost와 동일 소스(cost_imports)에서 집계 — ad_metrics 테이블은 채워주는
            // 배치가 없어 항상 비어있어서, 예전엔 cost는 정상인데 노출/클릭만 0으로 보이는 불일치가 있었음
            $channelCostImports = $costImports->where('platform', $platformMapping[$channel] ?? $channel);
            $totalImpressions = $channelCostImports->sum('impressions');
            $totalClicks = $channelCostImports->sum('clicks');

            $cpa = $totalLeads > 0 ? $totalCost / $totalLeads : 0;
            $roi = $totalCost > 0 ? (($totalRevenue - $totalCost) / $totalCost) * 100 : 0;
            $cpc = $totalClicks > 0 ? $totalCost / $totalClicks : 0;

            // CTR (클릭률) = 클릭 / 노출 × 100
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

            // CVR (전환율) = 전환 / 클릭 × 100
            $cvr = $totalClicks > 0 ? ($totalLeads / $totalClicks) * 100 : 0;

            // 전환율 (리드 → 예약)
            $conversionRate = $totalLeads > 0 ? ($totalAppointments / $totalLeads) * 100 : 0;

            // ROAS 계산
            $roas = $totalCost > 0 ? ($totalRevenue / $totalCost) * 100 : 0;

            return [
                'channel' => $channel,
                'category_code' => $categoryCode,
                'category_name' => $categoryName,
                'category_color' => $categoryColor,
                'impressions' => $totalImpressions,
                'clicks' => $totalClicks,
                'ctr' => round($ctr, 2),
                'leads' => $totalLeads,
                'cvr' => round($cvr, 2),
                'tickets' => $totalTickets,
                'appointments' => $totalAppointments,
                'contracts' => $totalContracts,
                'conversionRate' => round($conversionRate, 2),
                'cost' => round($totalCost),
                'revenue' => round($totalRevenue),
                'cpc' => round($cpc),
                'cpa' => round($cpa),
                'roi' => round($roi),
                'roas' => round($roas, 2),
            ];
        })->values()->toArray();
        Log::info('ChannelPerformanceData aggregated', ['data' => $channelPerformanceData]);

        // Aggregate category performance data (카테고리별 집계: 온라인/오프라인/DB)
        $categoryPerformanceData = $leads->groupBy('category_code')->map(function ($leadsByCategory, $categoryCode) use ($appointments, $costImports, $effectiveStatusByLeadId) {
            // 카테고리가 null인 경우 '기타'로 처리
            if (!$categoryCode) {
                $categoryCode = 'other';
                $categoryName = '기타';
                $categoryColor = '#9E9E9E';
            } else {
                $categoryName = $leadsByCategory->first()->category_name ?? $categoryCode;
                $categoryColor = $leadsByCategory->first()->category_color ?? '#000000';
            }

            $totalLeads = $leadsByCategory->count();

            // contacted 이상: tickets_count (상담 중인 리드) — pending/rejected는 마지막 도달 단계로 환산
            $totalTickets = $leadsByCategory->filter(function($lead) use ($effectiveStatusByLeadId) {
                $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                return in_array($status, ['contacted', 'scheduled', 'converted']);
            })->count();

            // scheduled 이상: appointments_count (약속 잡힌 리드)
            $totalAppointments = $leadsByCategory->filter(function($lead) use ($effectiveStatusByLeadId) {
                $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                return in_array($status, ['scheduled', 'converted']);
            })->count();

            // converted: contracts_count (계약 완료)
            $totalContracts = $leadsByCategory->filter(function($lead) {
                return $lead->status === 'converted';
            })->count();

            // converted: revenue 집계 (계약 완료된 리드의 수익)
            $totalRevenue = 0;
            $completedLeads = $leadsByCategory->filter(function($lead) {
                return $lead->status === 'converted';
            });
            foreach ($completedLeads as $lead) {
                $appointment = \App\Models\Appointment::where('lead_id', $lead->lead_id)->first();
                if ($appointment) {
                    $totalRevenue += $appointment->total_revenue ?? 0;
                }
            }

            // 카테고리에 속한 모든 채널의 비용 합산 (utm_source → cost_imports.platform 코드로 변환)
            $categoryChannels = $leadsByCategory->pluck('utm_source')->unique();
            $categoryPlatforms = $categoryChannels->map(fn ($channel) => self::PLATFORM_MAPPING[$channel] ?? $channel)->unique();
            $totalCost = $costImports->whereIn('platform', $categoryPlatforms)->sum('cost');

            // 노출/클릭도 cost와 동일하게 cost_imports에서 집계 (카테고리에 속한 모든 플랫폼)
            $totalImpressions = $costImports->whereIn('platform', $categoryPlatforms)->sum('impressions');
            $totalClicks = $costImports->whereIn('platform', $categoryPlatforms)->sum('clicks');

            $cpa = $totalLeads > 0 ? $totalCost / $totalLeads : 0;
            $roi = $totalCost > 0 ? (($totalRevenue - $totalCost) / $totalCost) * 100 : 0;
            $cpc = $totalClicks > 0 ? $totalCost / $totalClicks : 0;
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
            $cvr = $totalClicks > 0 ? ($totalLeads / $totalClicks) * 100 : 0;
            $conversionRate = $totalLeads > 0 ? ($totalAppointments / $totalLeads) * 100 : 0;

            // 카테고리 내 채널별 세부 성과 데이터 생성
            $channelDetails = $leadsByCategory->groupBy('utm_source')->map(function ($leadsByChannel, $channel) use ($costImports, $effectiveStatusByLeadId) {
                $channelLeads = $leadsByChannel->count();

                // 채널별 상태 집계 — pending/rejected는 마지막 도달 단계로 환산
                $channelTickets = $leadsByChannel->filter(function($lead) use ($effectiveStatusByLeadId) {
                    $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                    return in_array($status, ['contacted', 'scheduled', 'converted']);
                })->count();

                $channelAppointments = $leadsByChannel->filter(function($lead) use ($effectiveStatusByLeadId) {
                    $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
                    return in_array($status, ['scheduled', 'converted']);
                })->count();

                $channelContracts = $leadsByChannel->filter(function($lead) {
                    return $lead->status === 'converted';
                })->count();

                // 채널별 수익 집계
                $channelRevenue = 0;
                $completedChannelLeads = $leadsByChannel->filter(function($lead) {
                    return $lead->status === 'converted';
                });
                foreach ($completedChannelLeads as $lead) {
                    $appointment = \App\Models\Appointment::where('lead_id', $lead->lead_id)->first();
                    if ($appointment) {
                        $channelRevenue += $appointment->total_revenue ?? 0;
                    }
                }

                // 채널별 비용
                $platformMapping = self::PLATFORM_MAPPING;
                $channelCost = $costImports->where('platform', $platformMapping[$channel] ?? $channel)->sum('cost');

                // 채널별 노출/클릭 (cost와 동일하게 cost_imports에서 집계)
                $channelCostImportsForDetail = $costImports->where('platform', $platformMapping[$channel] ?? $channel);
                $channelImpressions = $channelCostImportsForDetail->sum('impressions');
                $channelClicks = $channelCostImportsForDetail->sum('clicks');

                $channelCpa = $channelLeads > 0 ? $channelCost / $channelLeads : 0;
                $channelRoi = $channelCost > 0 ? (($channelRevenue - $channelCost) / $channelCost) * 100 : 0;
                $channelCpc = $channelClicks > 0 ? $channelCost / $channelClicks : 0;
                $channelCtr = $channelImpressions > 0 ? ($channelClicks / $channelImpressions) * 100 : 0;
                $channelCvr = $channelClicks > 0 ? ($channelLeads / $channelClicks) * 100 : 0;
                $channelConversionRate = $channelLeads > 0 ? ($channelAppointments / $channelLeads) * 100 : 0;

                return [
                    'channel' => $channel ?? '알 수 없음',
                    'impressions' => $channelImpressions,
                    'clicks' => $channelClicks,
                    'ctr' => round($channelCtr, 2),
                    'cpc' => round($channelCpc),
                    'leads' => $channelLeads,
                    'cvr' => round($channelCvr, 2),
                    'tickets' => $channelTickets,
                    'appointments' => $channelAppointments,
                    'contracts' => $channelContracts,
                    'conversionRate' => round($channelConversionRate, 2),
                    'cost' => round($channelCost),
                    'revenue' => round($channelRevenue),
                    'cpa' => round($channelCpa),
                    'roi' => round($channelRoi),
                ];
            })->values()->toArray();

            return [
                'category_code' => $categoryCode,
                'category_name' => $categoryName,
                'category_color' => $categoryColor,
                'impressions' => $totalImpressions,
                'clicks' => $totalClicks,
                'ctr' => round($ctr, 2),
                'leads' => $totalLeads,
                'cvr' => round($cvr, 2),
                'tickets' => $totalTickets,
                'appointments' => $totalAppointments,
                'contracts' => $totalContracts,
                'conversionRate' => round($conversionRate, 2),
                'cost' => round($totalCost),
                'revenue' => round($totalRevenue),
                'cpc' => round($cpc),
                'cpa' => round($cpa),
                'roi' => round($roi),
                'channels' => $channelDetails, // 세부 채널 데이터 추가
            ];
        })->values()->toArray();
        Log::info('CategoryPerformanceData aggregated', ['data' => $categoryPerformanceData]);

        // Aggregate pivot table data (캠페인별 세부 데이터)
        // API 수집 채널(네이버 등)은 cost_imports.campaign_code(실제 캠페인명: 인사이트/파워컨텐츠#1/플레이스 등)
        // 기준으로 행을 분리해 캠페인별 실제 노출/클릭/비용을 그대로 보여준다.
        // 리드는 utm_campaign이 캠페인명과 정확히 일치할 때만 해당 행에 합쳐지고, 일치하지 않는
        // 리드 그룹(utm_campaign은 마케터 임의 입력값이라 대부분 불일치)은 비용 0인 별도 행으로 유지한다.
        // 이전의 "채널 총비용을 리드 수 비율로 분배" 방식은 캠페인별 실제 성과를 왜곡해서 폐기.
        $pivotTableData = collect();
        $emittedPlatforms = [];

        $leads->groupBy('utm_source')->each(function ($leadsByChannel, $channel) use (&$pivotTableData, &$emittedPlatforms, $costImports, $effectiveStatusByLeadId) {
            $channel = $channel ?: '알 수 없음';
            $platform = self::PLATFORM_MAPPING[$channel] ?? $channel;
            $apiCampaigns = $costImports->where('platform', $platform)->groupBy('campaign_code');

            // 같은 플랫폼으로 매핑되는 utm_source가 여러 개('네이버'/'naver')여도 API 캠페인 행은 한 번만 만든다
            $emitApiRows = $apiCampaigns->isNotEmpty() && !in_array($platform, $emittedPlatforms, true);
            if ($emitApiRows) {
                $emittedPlatforms[] = $platform;
            }

            $leadGroups = $leadsByChannel->groupBy('utm_campaign');

            if ($emitApiRows) {
                foreach ($apiCampaigns as $campaignCode => $campaignImports) {
                    $pivotTableData->push($this->buildPivotRow(
                        $channel,
                        $campaignCode !== '' ? $campaignCode : '알 수 없음',
                        $leadGroups->get($campaignCode, collect()),
                        (int) $campaignImports->sum('impressions'),
                        (int) $campaignImports->sum('clicks'),
                        (float) $campaignImports->sum('cost'),
                        'api',
                        $effectiveStatusByLeadId
                    ));
                }
            }

            foreach ($leadGroups as $campaign => $leadsByCampaign) {
                if ($emitApiRows && $campaign !== '' && $apiCampaigns->has($campaign)) {
                    continue; // 위 API 캠페인 행에 이미 합쳐짐
                }
                $pivotTableData->push($this->buildPivotRow(
                    $channel,
                    $campaign !== '' && $campaign !== null ? $campaign : '알 수 없음',
                    $leadsByCampaign,
                    0,
                    0,
                    0,
                    'manual',
                    $effectiveStatusByLeadId
                ));
            }
        });

        // 해당 기간에 리드가 한 건도 없는 플랫폼의 광고비도 캠페인별로 표시한다
        // (예: 네이버 유입 리드가 0건이어도 광고비는 집행되고 있으므로 화면에서 사라지면 안 됨)
        $costImports->groupBy('platform')->each(function ($platformImports, $platform) use (&$pivotTableData, $emittedPlatforms, $effectiveStatusByLeadId) {
            if (in_array($platform, $emittedPlatforms, true)) {
                return;
            }
            $channelLabel = self::PLATFORM_DISPLAY_NAMES[$platform] ?? $platform;
            foreach ($platformImports->groupBy('campaign_code') as $campaignCode => $campaignImports) {
                $pivotTableData->push($this->buildPivotRow(
                    $channelLabel,
                    $campaignCode !== '' ? $campaignCode : '알 수 없음',
                    collect(),
                    (int) $campaignImports->sum('impressions'),
                    (int) $campaignImports->sum('clicks'),
                    (float) $campaignImports->sum('cost'),
                    'api',
                    $effectiveStatusByLeadId
                ));
            }
        });

        Log::info('PivotTableData aggregated', ['data' => $pivotTableData->toArray()]);


        return Response::json([
            'channelPerformance' => $channelPerformanceData,
            'categoryPerformance' => $categoryPerformanceData,
            'pivotTable' => $pivotTableData->toArray(),
        ]);
    }

    /**
     * 피벗 테이블 행 하나를 생성한다.
     * 노출/클릭/비용은 호출부에서 cost_imports 실측값(API 캠페인 행) 또는 0(리드 전용 행)으로 전달하고,
     * 리드 퍼널 지표(리드/상담/예약/계약/수익)는 전달받은 리드 그룹에서 집계한다.
     *
     * @param \Illuminate\Support\Collection $campaignLeads 이 캠페인에 귀속된 리드 목록 (없으면 빈 컬렉션)
     * @param array<string, string> $effectiveStatusByLeadId pending/rejected 리드의 마지막 도달 단계 맵
     */
    private function buildPivotRow(string $channel, string $campaign, $campaignLeads, int $impressions, int $clicks, float $cost, string $source, array $effectiveStatusByLeadId): array
    {
        $totalLeads = $campaignLeads->count();

        // 상태 기반 카운팅 — pending/rejected는 마지막 도달 단계로 환산
        $totalTickets = $campaignLeads->filter(function ($lead) use ($effectiveStatusByLeadId) {
            $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
            return in_array($status, ['contacted', 'scheduled', 'converted']);
        })->count();

        $totalAppointments = $campaignLeads->filter(function ($lead) use ($effectiveStatusByLeadId) {
            $status = $effectiveStatusByLeadId[$lead->lead_id] ?? $lead->status;
            return in_array($status, ['scheduled', 'converted']);
        })->count();

        $convertedLeads = $campaignLeads->filter(fn ($lead) => $lead->status === 'converted');
        $totalContracts = $convertedLeads->count();

        $totalRevenue = 0;
        foreach ($convertedLeads as $lead) {
            $appointment = Appointment::where('lead_id', $lead->lead_id)->first();
            if ($appointment) {
                $totalRevenue += $appointment->total_revenue ?? 0;
            }
        }

        $cpa = $totalLeads > 0 ? $cost / $totalLeads : 0;
        $roas = $cost > 0 ? ($totalRevenue / $cost) * 100 : 0;
        $cpc = $clicks > 0 ? $cost / $clicks : 0;
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        $cvr = $clicks > 0 ? ($totalLeads / $clicks) * 100 : 0;
        $conversionRate = $totalLeads > 0 ? ($totalAppointments / $totalLeads) * 100 : 0;

        return [
            'id' => uniqid(), // Temporary ID
            'channel' => $channel,
            'campaign' => $campaign,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => round($ctr, 2),
            'leads' => $totalLeads,
            'cvr' => round($cvr, 2),
            'tickets' => $totalTickets,
            'appointments' => $totalAppointments,
            'contracts' => $totalContracts,
            'conversionRate' => round($conversionRate, 2),
            'cost' => round($cost),
            'revenue' => round($totalRevenue),
            'cpc' => round($cpc),
            'cpa' => round($cpa),
            'roas' => round($roas),
            'source' => $source, // API 데이터 vs 수동 입력 데이터 구분
        ];
    }

    /**
     * pending/rejected 리드를 "마지막으로 도달했던 단계"로 환산한 맵을 반환한다.
     * (new/contacted/scheduled/converted 상태인 리드는 호출부에서 현재 status를 그대로 사용)
     * dropoffs()와 동일한 audit_logs 역추적 로직을 리드별 쿼리 대신 배치 쿼리로 처리한다.
     *
     * @return array<string, string> lead_id => effective status (new|contacted|scheduled|converted)
     */
    private function resolveEffectiveStatuses($leads): array
    {
        $pendingRejected = $leads->whereIn('status', ['pending', 'rejected']);
        $leadIds = $pendingRejected->pluck('lead_id')->unique()->values()->toArray();

        if (empty($leadIds)) {
            return [];
        }

        $logsByLead = DB::table('audit_logs')
            ->where('target_type', 'Lead')
            ->whereIn('target_id', $leadIds)
            ->orderByDesc('at')
            ->get(['target_id', 'old_values', 'new_values'])
            ->groupBy('target_id');

        $effectiveStatus = [];
        foreach ($pendingRejected as $lead) {
            $logs = $logsByLead->get($lead->lead_id, collect());
            $matchingLog = $logs->first(function ($log) use ($lead) {
                $newValues = json_decode($log->new_values, true);
                return ($newValues['status'] ?? null) === $lead->status;
            });

            $prevStatus = null;
            if ($matchingLog && $matchingLog->old_values) {
                $oldValues = json_decode($matchingLog->old_values, true);
                $prevStatus = $oldValues['status'] ?? null;
            }

            $effectiveStatus[$lead->lead_id] = in_array($prevStatus, ['contacted', 'scheduled', 'converted'])
                ? $prevStatus
                : 'new';
        }

        return $effectiveStatus;
    }

    /**
     * 퍼널 단계별 이탈(보류/거절) 리드 목록
     * audit_logs에서 status가 pending/rejected로 바뀐 시점의 이전 status를 읽어
     * "마지막으로 도달했던 단계"를 판별한다.
     */
    public function dropoffs(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $leadsQuery = Lead::query()
            ->leftJoin('visits', 'leads.source_visit_id', '=', 'visits.visit_id')
            ->leftJoin('users', 'leads.assigned_user_id', '=', 'users.user_id')
            ->whereIn('leads.status', ['pending', 'rejected'])
            ->select(
                'leads.*',
                DB::raw("COALESCE(visits.utm_source, '채널 미확인') as utm_source"),
                'users.name as assignee_name'
            );

        if ($startDate) {
            $leadsQuery->whereDate('leads.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $leadsQuery->whereDate('leads.created_at', '<=', $endDate);
        }

        $leads = $leadsQuery->get();

        $stageLabel = [
            'new' => '문의',
            'contacted' => '상담',
            'scheduled' => '예약',
            'converted' => '계약',
        ];

        $dropoffs = $leads->map(function ($lead) use ($stageLabel) {
            $event = DB::table('audit_logs')
                ->where('target_type', 'Lead')
                ->where('target_id', $lead->lead_id)
                ->where('new_values->status', $lead->status)
                ->orderByDesc('at')
                ->first();

            $prevStatus = null;
            if ($event && $event->old_values) {
                $oldValues = json_decode($event->old_values, true);
                $prevStatus = $oldValues['status'] ?? null;
            }

            return [
                'lead_id' => $lead->lead_id,
                'name' => $lead->name,
                'primary_phone' => $lead->primary_phone,
                'utm_source' => $lead->utm_source,
                'assignee_name' => $lead->assignee_name ?? '미배정',
                'status' => $lead->status,
                'last_stage' => $stageLabel[$prevStatus] ?? '문의',
                'dropped_at' => $event->at ?? $lead->created_at,
                'memo' => $lead->memo,
            ];
        });

        return Response::json(['dropoffs' => $dropoffs->values()]);
    }
}
