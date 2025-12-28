<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingInsight;
use App\Models\ChannelTreatmentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MarketingInsightController extends Controller
{
    /**
     * 인사이트 목록 조회
     */
    public function index(Request $request)
    {
        $query = MarketingInsight::with('generator');

        // 필터링
        if ($request->has('insight_type')) {
            $query->byType($request->insight_type);
        }

        if ($request->has('is_published')) {
            if ($request->is_published === 'true' || $request->is_published === '1') {
                $query->published();
            }
        }

        $insights = $query->latest()->paginate(20);

        return response()->json($insights);
    }

    /**
     * 특정 인사이트 조회
     */
    public function show($id)
    {
        $insight = MarketingInsight::with('generator')->findOrFail($id);
        return response()->json($insight);
    }

    /**
     * AI 분석 실행 및 인사이트 생성
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'insight_type' => 'required|in:channel_performance,treatment_trend,recommendation,roi_analysis',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $type = $request->insight_type;

        // 데이터 분석
        $analysis = $this->analyzeData($startDate, $endDate, $type);

        // AI 분석 결과 생성 (실제 AI 연동 시 OpenAI API 등 사용)
        $content = $this->generateAIContent($analysis, $type);
        $recommendations = $this->generateRecommendations($analysis, $type);

        // 인사이트 저장
        $insight = MarketingInsight::create([
            'analysis_period_start' => $startDate,
            'analysis_period_end' => $endDate,
            'insight_type' => $type,
            'title' => $this->generateTitle($type, $startDate, $endDate),
            'content' => $content,
            'recommendations' => $recommendations,
            'confidence_score' => $this->calculateConfidenceScore($analysis),
            'generated_by' => auth()->user()->user_id ?? null,
            'is_published' => false,
        ]);

        return response()->json([
            'message' => 'Insight generated successfully',
            'insight' => $insight,
        ], 201);
    }

    /**
     * 인사이트 공개/비공개 전환
     */
    public function togglePublish($id)
    {
        $insight = MarketingInsight::findOrFail($id);
        $insight->is_published = !$insight->is_published;
        $insight->save();

        return response()->json([
            'message' => 'Publish status updated',
            'insight' => $insight,
        ]);
    }

    /**
     * 인사이트 삭제
     */
    public function destroy($id)
    {
        $insight = MarketingInsight::findOrFail($id);
        $insight->delete();

        return response()->json([
            'message' => 'Insight deleted successfully',
        ]);
    }

    /**
     * 데이터 분석 (채널별, 진료별 통계)
     */
    private function analyzeData($startDate, $endDate, $type)
    {
        $records = ChannelTreatmentRecord::with(['channelCategory', 'treatmentType'])
            ->dateRange($startDate, $endDate)
            ->get();

        $analysis = [
            'total_count' => $records->sum('count'),
            'total_revenue' => $records->sum('revenue'),
            'by_channel' => [],
            'by_treatment' => [],
            'trends' => [],
        ];

        // 채널별 집계
        $byChannel = $records->groupBy('channel_category_id');
        foreach ($byChannel as $channelId => $channelRecords) {
            $channelName = $channelRecords->first()->channelCategory->name ?? 'Unknown';
            $analysis['by_channel'][$channelName] = [
                'count' => $channelRecords->sum('count'),
                'revenue' => $channelRecords->sum('revenue'),
                'avg_revenue_per_patient' => $channelRecords->sum('count') > 0
                    ? $channelRecords->sum('revenue') / $channelRecords->sum('count')
                    : 0,
            ];
        }

        // 진료 유형별 집계
        $byTreatment = $records->groupBy('treatment_type_id');
        foreach ($byTreatment as $treatmentId => $treatmentRecords) {
            $treatmentName = $treatmentRecords->first()->treatmentType->name ?? 'Unknown';
            $analysis['by_treatment'][$treatmentName] = [
                'count' => $treatmentRecords->sum('count'),
                'revenue' => $treatmentRecords->sum('revenue'),
            ];
        }

        return $analysis;
    }

    /**
     * AI 컨텐츠 생성 (실제 구현 시 OpenAI API 연동)
     */
    private function generateAIContent($analysis, $type)
    {
        // 임시 구현: 실제로는 OpenAI GPT API 호출
        $content = [
            'summary' => $this->generateSummary($analysis, $type),
            'key_findings' => $this->generateKeyFindings($analysis, $type),
            'detailed_analysis' => $analysis,
        ];

        return $content;
    }

    /**
     * 요약 생성
     */
    private function generateSummary($analysis, $type)
    {
        switch ($type) {
            case 'channel_performance':
                $topChannel = collect($analysis['by_channel'])->sortByDesc('revenue')->first();
                return "분석 기간 동안 가장 높은 성과를 보인 채널은 " . array_search($topChannel, $analysis['by_channel']) . "입니다. 총 매출: " . number_format($topChannel['revenue']) . "원";

            case 'treatment_trend':
                $topTreatment = collect($analysis['by_treatment'])->sortByDesc('count')->first();
                return "가장 많은 환자가 받은 진료는 " . array_search($topTreatment, $analysis['by_treatment']) . "이며, 총 " . $topTreatment['count'] . "건 진행되었습니다.";

            case 'recommendation':
                return "데이터 분석 결과를 기반으로 마케팅 전략 개선 방안을 제안합니다.";

            default:
                return "종합 분석 결과";
        }
    }

    /**
     * 주요 발견사항 생성
     */
    private function generateKeyFindings($analysis, $type)
    {
        $findings = [];

        // 상위 3개 채널
        $topChannels = collect($analysis['by_channel'])
            ->sortByDesc('revenue')
            ->take(3)
            ->keys()
            ->toArray();

        $findings[] = "상위 수익 채널: " . implode(', ', $topChannels);

        // 상위 3개 진료
        $topTreatments = collect($analysis['by_treatment'])
            ->sortByDesc('revenue')
            ->take(3)
            ->keys()
            ->toArray();

        $findings[] = "주요 진료 항목: " . implode(', ', $topTreatments);

        return $findings;
    }

    /**
     * 마케팅 제안 생성
     */
    private function generateRecommendations($analysis, $type)
    {
        $recommendations = [];

        // 저성과 채널 개선 제안
        $lowPerformingChannels = collect($analysis['by_channel'])
            ->sortBy('revenue')
            ->take(2);

        foreach ($lowPerformingChannels as $channel => $data) {
            $recommendations[] = [
                'type' => 'channel_improvement',
                'priority' => 'high',
                'title' => "{$channel} 채널 강화 전략",
                'description' => "{$channel} 채널의 성과가 낮습니다. 광고 예산 재배분 또는 콘텐츠 개선을 고려하세요.",
                'expected_impact' => '매출 20-30% 향상 예상',
            ];
        }

        // 고성과 진료 확대 제안
        $topTreatment = collect($analysis['by_treatment'])->sortByDesc('revenue')->first();
        $topTreatmentName = collect($analysis['by_treatment'])->sortByDesc('revenue')->keys()->first();

        $recommendations[] = [
            'type' => 'treatment_expansion',
            'priority' => 'medium',
            'title' => "{$topTreatmentName} 진료 마케팅 강화",
            'description' => "{$topTreatmentName}이(가) 높은 수익을 창출하고 있습니다. 해당 진료를 중점적으로 홍보하세요.",
            'expected_impact' => '신규 환자 유입 증가 예상',
        ];

        return $recommendations;
    }

    /**
     * 제목 생성
     */
    private function generateTitle($type, $startDate, $endDate)
    {
        $typeLabels = [
            'channel_performance' => '채널 성과 분석',
            'treatment_trend' => '진료 트렌드 분석',
            'recommendation' => '마케팅 전략 제안',
            'roi_analysis' => 'ROI 분석',
        ];

        return ($typeLabels[$type] ?? '분석') . " ({$startDate} ~ {$endDate})";
    }

    /**
     * 신뢰도 점수 계산
     */
    private function calculateConfidenceScore($analysis)
    {
        // 데이터 양에 기반한 신뢰도 점수
        $totalRecords = $analysis['total_count'];

        if ($totalRecords >= 100) {
            return 95.0;
        } elseif ($totalRecords >= 50) {
            return 85.0;
        } elseif ($totalRecords >= 20) {
            return 70.0;
        } else {
            return 50.0;
        }
    }
}
