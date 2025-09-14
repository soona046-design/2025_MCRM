<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성을 위해 추가
use App\Models\Visit; // Visit 모델을 사용하기 위해 추가 (아직 생성 안 됨, 나중에 생성할 것)

class VisitController extends Controller
{
    public function collectVisit(Request $request)
    {
        // 유효성 검사 (필요에 따라 상세하게 정의)
        $request->validate([
            'clientId' => 'nullable|string',
            'sessionId' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_content' => 'nullable|string',
            'utm_term' => 'nullable|string',
            'referrer' => 'nullable|string',
            'landing_path' => 'nullable|string',
            'ts' => 'nullable|date', // timestamp
        ]);

        $visitId = Str::uuid(); // UUID 생성

        // 'utm' 객체로부터 개별 UTM 파라미터 추출
        $utm = $request->input('utm', []);

        $visit = Visit::create([
            'visit_id' => $visitId,
            'client_id' => $request->input('clientId'),
            'session_id' => $request->input('sessionId'),
            'utm_source' => $utm['source'] ?? $request->input('utm_source'),
            'utm_medium' => $utm['medium'] ?? $request->input('utm_medium'),
            'utm_campaign' => $utm['campaign'] ?? $request->input('utm_campaign'),
            'utm_content' => $utm['content'] ?? $request->input('utm_content'),
            'utm_term' => $utm['term'] ?? $request->input('utm_term'),
            'referrer' => $request->input('referrer'),
            'landing_path' => $request->input('landing_path'),
            'first_seen_at' => $request->input('ts') ? \Carbon\Carbon::parse($request->input('ts')) : now(),
            'ip' => $request->ip(), // 요청자의 IP 주소 자동 저장
            'ua' => $request->header('User-Agent'), // User Agent 자동 저장
        ]);

        return response()->json(['visitId' => $visit->visit_id], 201);
    }
}
