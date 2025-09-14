<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성을 위해 추가
use App\Models\Lead; // Lead 모델을 사용하기 위해 추가
use App\Models\Visit; // Visit 모델을 사용하기 위해 추가 (source_visit_id 참조)

class LeadController extends Controller
{
    /**
     * 새로운 리드를 생성합니다.
     */
    public function store(Request $request)
    {
        // 유효성 검사
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required_without:email|string|max:20|nullable', // phone 또는 email 중 하나는 필수
            'email' => 'required_without:phone|email|max:255|nullable', // phone 또는 email 중 하나는 필수
            'consent' => 'nullable|array', // consent_flags
            'sourceVisitId' => 'nullable|uuid|exists:visits,visit_id', // visits 테이블의 visit_id 참조
            'memo' => 'nullable|string', // 명세서에는 없지만, 임시로 추가
        ]);

        // 중복 리드 병합 로직 (전화번호, 해시 이메일 등) - 여기서는 단순 생성
        // 실제 구현에서는 기존 리드를 검색하여 업데이트하거나 병합해야 합니다.
        $existingLead = null;
        if (isset($validatedData['phone'])) {
            $existingLead = Lead::where('primary_phone', $validatedData['phone'])->first();
        } elseif (isset($validatedData['email'])) {
            // 이메일 해싱 로직 필요 (명세서의 email_hash 필드)
            $emailHash = hash('sha256', strtolower($validatedData['email']));
            $existingLead = Lead::where('email_hash', $emailHash)->first();
        }

        if ($existingLead) {
            // TODO: 기존 리드 업데이트 또는 병합 로직 구현
            return response()->json([
                'leadId' => $existingLead->lead_id,
                'score' => $existingLead->score,
                'message' => 'Existing lead updated or merged.',
            ], 200);
        }

        $leadId = Str::uuid(); // UUID 생성

        $lead = Lead::create([
            'lead_id' => $leadId,
            'primary_phone' => $validatedData['phone'] ?? null,
            'email_hash' => isset($validatedData['email']) ? hash('sha256', strtolower($validatedData['email'])) : null,
            'name' => $validatedData['name'] ?? null,
            'consent_flags' => $validatedData['consent'] ?? null,
            'source_visit_id' => $validatedData['sourceVisitId'] ?? null,
            'status' => 'new', // 초기 상태는 'new'
            'score' => 0, // 초기 점수 0
        ]);

        // 리드 스코어링 로직 (규칙 기반) - TODO: 여기에 구현
        // 명세서: 채널·페이지 체류·버튼 클릭 수·업무시간 외 문의 등 가중치.
        // $lead->score = $this->calculateLeadScore($lead, $request);
        // $lead->save();

        return response()->json([
            'leadId' => $lead->lead_id,
            'score' => $lead->score,
        ], 201);
    }

    // TODO: 리드 스코어링 로직을 위한 private 메소드 추가 가능
    // private function calculateLeadScore(Lead $lead, Request $request)
    // {
    //     $score = 0;
    //     // 예시: 특정 채널에서 유입된 경우 점수 추가
    //     if ($lead->visit && $lead->visit->utm_source === 'naver_cpc') {
    //         $score += 10;
    //     }
    //     // TODO: 페이지 체류, 버튼 클릭 수, 업무시간 외 문의 등 로직 추가
    //     return $score;
    // }
}