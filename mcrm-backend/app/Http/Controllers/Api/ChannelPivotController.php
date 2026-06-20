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
use App\Models\AdMetric;
use App\Services\Ads\NaverAdsApiService;

class ChannelPivotController extends Controller
{
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
                $naverCosts = $this->naverAdsApiService->getAdCosts($startDate, $endDate);
                Log::info('Naver Ads API costs fetched', ['count' => count($naverCosts)]);

                // 기존 데이터 삭제 (Naver)
                CostImport::where('platform', '네이버')->whereBetween('date', [$startDate, $endDate])->delete();

                // 기존 데이터 삭제 (Facebook)
                CostImport::where('platform', 'Facebook')->whereBetween('date', [$startDate, $endDate])->delete();

                foreach ($naverCosts as $costData) {
                    CostImport::create($costData);
                }
                Log::info('Naver Ads costs stored/updated in CostImport.', ['naverCosts' => $naverCosts]);

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

        // Fetch AdMetric data (광고 성과 지표)
        $adMetricsQuery = AdMetric::query();
        if ($startDate) {
            $adMetricsQuery->whereDate('date_start', '>=', $startDate);
        }
        if ($endDate) {
            $adMetricsQuery->whereDate('date_end', '<=', $endDate);
        }
        $adMetrics = $adMetricsQuery->get();
        Log::info('AdMetrics fetched', ['count' => $adMetrics->count(), 'first_ad_metric' => $adMetrics->first()]);

        Log::info('Leads utm_sources before grouping', $leads->pluck('utm_source')->toArray());

        // Aggregate channel performance data (채널별 집계)
        $channelPerformanceData = $leads->groupBy('utm_source')->map(function ($leadsByChannel, $channel) use ($appointments, $costImports, $adMetrics, $leads) {
            $totalLeads = $leadsByChannel->count();

            // 카테고리 정보 가져오기 (첫 번째 lead에서)
            $firstLead = $leadsByChannel->first();
            $categoryCode = $firstLead->category_code ?? null;
            $categoryName = $firstLead->category_name ?? '알 수 없음';
            $categoryColor = $firstLead->category_color ?? '#9E9E9E';

            // 누적 상태 기반 카운팅 (퍼널: 문의→상담→예약→계약)
            // new: leads (전환수) - 모든 리드가 new 이상
            // contacted 이상: tickets_count (상담수)
            $totalTickets = $leadsByChannel->filter(function($lead) {
                return in_array($lead->status, ['contacted', 'scheduled', 'converted']);
            })->count();

            // scheduled 이상: appointments_count (예약수)
            $totalAppointments = $leadsByChannel->filter(function($lead) {
                return in_array($lead->status, ['scheduled', 'converted']);
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

            $totalCost = $costImports->where('platform', $channel)->sum('cost');

            // AdMetric 데이터 집계 (플랫폼 매핑)
            $platformMapping = [
                '네이버' => 'naver',
                'Google Ads' => 'google',
                'Facebook Ads' => 'meta',
            ];
            $adPlatform = $platformMapping[$channel] ?? null;
            $totalImpressions = 0;
            $totalClicks = 0;

            if ($adPlatform) {
                $channelAdMetrics = $adMetrics->where('platform', $adPlatform);
                $totalImpressions = $channelAdMetrics->sum('impressions');
                $totalClicks = $channelAdMetrics->sum('clicks');
            }

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
        $categoryPerformanceData = $leads->groupBy('category_code')->map(function ($leadsByCategory, $categoryCode) use ($appointments, $costImports, $adMetrics) {
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

            // contacted 이상: tickets_count (상담 중인 리드)
            $totalTickets = $leadsByCategory->filter(function($lead) {
                return in_array($lead->status, ['contacted', 'scheduled', 'converted']);
            })->count();

            // scheduled 이상: appointments_count (약속 잡힌 리드)
            $totalAppointments = $leadsByCategory->filter(function($lead) {
                return in_array($lead->status, ['scheduled', 'converted']);
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

            // 카테고리에 속한 모든 채널의 비용 합산
            $categoryChannels = $leadsByCategory->pluck('utm_source')->unique();
            $totalCost = $costImports->whereIn('platform', $categoryChannels)->sum('cost');

            // AdMetric 데이터 집계 (카테고리에 속한 모든 플랫폼)
            $totalImpressions = 0;
            $totalClicks = 0;

            foreach ($categoryChannels as $channel) {
                $platformMapping = [
                    '네이버' => 'naver',
                    'Google Ads' => 'google',
                    'Facebook Ads' => 'meta',
                ];
                $adPlatform = $platformMapping[$channel] ?? null;

                if ($adPlatform) {
                    $channelAdMetrics = $adMetrics->where('platform', $adPlatform);
                    $totalImpressions += $channelAdMetrics->sum('impressions');
                    $totalClicks += $channelAdMetrics->sum('clicks');
                }
            }

            $cpa = $totalLeads > 0 ? $totalCost / $totalLeads : 0;
            $roi = $totalCost > 0 ? (($totalRevenue - $totalCost) / $totalCost) * 100 : 0;
            $cpc = $totalClicks > 0 ? $totalCost / $totalClicks : 0;
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
            $cvr = $totalClicks > 0 ? ($totalLeads / $totalClicks) * 100 : 0;
            $conversionRate = $totalLeads > 0 ? ($totalAppointments / $totalLeads) * 100 : 0;

            // 카테고리 내 채널별 세부 성과 데이터 생성
            $channelDetails = $leadsByCategory->groupBy('utm_source')->map(function ($leadsByChannel, $channel) use ($costImports, $adMetrics) {
                $channelLeads = $leadsByChannel->count();

                // 채널별 상태 집계
                $channelTickets = $leadsByChannel->filter(function($lead) {
                    return in_array($lead->status, ['contacted', 'scheduled', 'converted']);
                })->count();

                $channelAppointments = $leadsByChannel->filter(function($lead) {
                    return in_array($lead->status, ['scheduled', 'converted']);
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
                $channelCost = $costImports->where('platform', $channel)->sum('cost');

                // 채널별 AdMetric 데이터
                $platformMapping = [
                    '네이버' => 'naver',
                    'Google Ads' => 'google',
                    'Facebook Ads' => 'meta',
                ];
                $adPlatform = $platformMapping[$channel] ?? null;

                $channelImpressions = 0;
                $channelClicks = 0;
                if ($adPlatform) {
                    $channelAdMetrics = $adMetrics->where('platform', $adPlatform);
                    $channelImpressions = $channelAdMetrics->sum('impressions');
                    $channelClicks = $channelAdMetrics->sum('clicks');
                }

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
        // 중첩된 groupBy 처리
        $pivotTableData = collect();

        $leads->groupBy('utm_source')->each(function ($leadsByChannel, $channel) use (&$pivotTableData, $appointments, $costImports, $adMetrics) {
            $channel = $channel ?? '알 수 없음';

            $leadsByChannel->groupBy('utm_campaign')->each(function ($leadsByChannelCampaign, $campaign) use (&$pivotTableData, $channel, $appointments, $costImports, $adMetrics) {
                $campaign = $campaign ?? '알 수 없음';

                $totalLeads = $leadsByChannelCampaign->count();

            // 상태 기반 카운팅
            // contacted 이상: tickets_count (상담 중인 리드)
            $totalTickets = $leadsByChannelCampaign->filter(function($lead) {
                return in_array($lead->status, ['contacted', 'scheduled', 'converted']);
            })->count();

            // scheduled 이상: appointments_count (약속 잡힌 리드)
            $totalAppointments = $leadsByChannelCampaign->filter(function($lead) {
                return in_array($lead->status, ['scheduled', 'converted']);
            })->count();

            // converted: contracts_count (계약 완료)
            $totalContracts = $leadsByChannelCampaign->filter(function($lead) {
                return $lead->status === 'converted';
            })->count();

            // converted: revenue 집계 (계약 완료된 리드의 수익)
            $totalRevenue = 0;
            $completedLeads = $leadsByChannelCampaign->filter(function($lead) {
                return $lead->status === 'converted';
            });
            foreach ($completedLeads as $lead) {
                $appointment = \App\Models\Appointment::where('lead_id', $lead->lead_id)->first();
                if ($appointment) {
                    $totalRevenue += $appointment->total_revenue ?? 0;
                }
            }

            $totalCost = $costImports->where('platform', $channel)->where('campaign', $campaign)->sum('cost');

            // AdMetric 데이터 집계 (플랫폼 매핑)
            $platformMapping = [
                '네이버' => 'naver',
                'Google Ads' => 'google',
                'Facebook Ads' => 'meta',
            ];
            $adPlatform = $platformMapping[$channel] ?? null;
            $totalImpressions = 0;
            $totalClicks = 0;

            if ($adPlatform) {
                // 캠페인별로도 필터링 (meta_json에 campaign 정보가 있다면)
                $campaignAdMetrics = $adMetrics->where('platform', $adPlatform);
                $totalImpressions = $campaignAdMetrics->sum('impressions');
                $totalClicks = $campaignAdMetrics->sum('clicks');
            }

            $cpa = $totalLeads > 0 ? $totalCost / $totalLeads : 0;
            $roas = $totalCost > 0 ? ($totalRevenue / $totalCost) * 100 : 0;
            $cpc = $totalClicks > 0 ? $totalCost / $totalClicks : 0;

            // CTR (클릭률) = 클릭 / 노출 × 100
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

            // CVR (전환율) = 전환 / 클릭 × 100
            $cvr = $totalClicks > 0 ? ($totalLeads / $totalClicks) * 100 : 0;

            // 전환율 (리드 → 예약)
            $conversionRate = $totalLeads > 0 ? ($totalAppointments / $totalLeads) * 100 : 0;

            // 데이터 출처 판단: AdMetrics 데이터가 있으면 API, 없으면 수동 입력
            $source = ($totalImpressions > 0 || $totalClicks > 0) ? 'api' : 'manual';

            $pivotTableData->push([
                'id' => uniqid(), // Temporary ID
                'channel' => $channel,
                'campaign' => $campaign,
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
                'roas' => round($roas),
                'source' => $source, // API 데이터 vs 수동 입력 데이터 구분
            ]);
            });
        });

        Log::info('PivotTableData aggregated', ['data' => $pivotTableData->toArray()]);


        return Response::json([
            'channelPerformance' => $channelPerformanceData,
            'categoryPerformance' => $categoryPerformanceData,
            'pivotTable' => $pivotTableData->toArray(),
        ]);
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
