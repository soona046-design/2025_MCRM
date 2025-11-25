<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ChannelCategoryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Visit;

class VisitController extends Controller
{
    /**
     * 모든 방문 목록 조회
     */
    public function index(Request $request)
    {
        $query = Visit::query();

        // 필터링
        if ($request->has('utm_source')) {
            $query->where('utm_source', $request->utm_source);
        }

        if ($request->has('channel_category')) {
            $query->where('channel_category', $request->channel_category);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $visits = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json($visits);
    }

    /**
     * 방문 상세 조회
     */
    public function show($id)
    {
        $visit = Visit::findOrFail($id);

        return response()->json($visit);
    }

    /**
     * 방문 기록 저장 (공개 API)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|string|max:255',
            'session_id' => 'nullable|string|max:255',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:500',
            'landing_path' => 'nullable|string|max:500',
            'ts' => 'nullable|numeric',
        ]);

        // 'utm' 객체로부터 개별 UTM 파라미터 추출
        $utm = $request->input('utm', []);
        $utmSource = $utm['source'] ?? $validated['utm_source'] ?? null;
        $utmMedium = $utm['medium'] ?? $validated['utm_medium'] ?? null;
        $utmCampaign = $utm['campaign'] ?? $validated['utm_campaign'] ?? null;
        $utmContent = $utm['content'] ?? $validated['utm_content'] ?? null;
        $utmTerm = $utm['term'] ?? $validated['utm_term'] ?? null;

        // 자동 채널 카테고리 할당
        $channelCategory = ChannelCategoryHelper::getCategoryFromUtmSource($utmSource);

        $visit = Visit::create([
            'client_id' => $validated['client_id'],
            'session_id' => $validated['session_id'],
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content' => $utmContent,
            'utm_term' => $utmTerm,
            'referrer' => $validated['referrer'],
            'landing_path' => $validated['landing_path'],
            'channel_category' => $channelCategory,
            'first_seen_at' => $validated['ts'] ? \Carbon\Carbon::createFromTimestamp($validated['ts'])->toDateTime() : now(),
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ]);

        return response()->json([
            'id' => $visit->id,
            'channel_category' => $visit->channel_category,
            'utm_source' => $visit->utm_source,
        ], 201);
    }

    /**
     * 방문 기록 수정
     */
    public function update(Request $request, $id)
    {
        $visit = Visit::findOrFail($id);

        $validated = $request->validate([
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:500',
            'landing_path' => 'nullable|string|max:500',
            'channel_category' => 'nullable|in:online,offline,db',
        ]);

        // utm_source가 변경되었으면 카테고리 자동 재계산
        if (isset($validated['utm_source']) && $validated['utm_source'] !== $visit->utm_source) {
            $validated['channel_category'] = ChannelCategoryHelper::getCategoryFromUtmSource($validated['utm_source']);
        }

        $visit->update($validated);

        return response()->json($visit);
    }

    /**
     * 방문 기록 삭제
     */
    public function destroy($id)
    {
        $visit = Visit::findOrFail($id);
        $visit->delete();

        return response()->json(['message' => '방문 기록이 삭제되었습니다.']);
    }

    /**
     * 수집 API (하위 호환성 유지)
     */
    public function collectVisit(Request $request)
    {
        return $this->store($request);
    }

    /**
     * 카테고리별 방문 통계
     */
    public function statisticsByCategory(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30));
        $dateTo = $request->input('date_to', now());

        $stats = Visit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('channel_category, COUNT(*) as count, COUNT(DISTINCT client_id) as unique_clients')
            ->groupBy('channel_category')
            ->get()
            ->map(function ($stat) {
                return [
                    'category' => $stat->channel_category,
                    'category_name' => ChannelCategoryHelper::getCategoryName($stat->channel_category ?? 'unknown'),
                    'category_color' => ChannelCategoryHelper::getCategoryColor($stat->channel_category ?? 'unknown'),
                    'count' => $stat->count,
                    'unique_clients' => $stat->unique_clients,
                ];
            });

        return response()->json($stats);
    }
}
