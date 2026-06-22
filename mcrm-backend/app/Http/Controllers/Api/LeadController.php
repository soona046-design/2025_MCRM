<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성에 필요
use App\Models\Lead; // Lead 모델 사용
use App\Models\User; // User 모델 사용 (담당자 조회 시 필요)
use Illuminate\Validation\Rule; // 유효성 검사 룰 사용
use Illuminate\Support\Facades\Hash; // 이메일 해싱에 필요
use App\Models\Visit; // Visit 모델 사용
use App\Helpers\ChannelCategoryHelper; // 채널 카테고리 자동 분류

class LeadController extends Controller
{
    /**
     * 새로운 리드를 저장합니다。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'primary_phone' => 'nullable|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['new', 'contacted', 'scheduled', 'converted', 'pending', 'rejected'])],
            'score' => 'nullable|integer|min:0|max:100',
            'memo' => 'nullable|string',
            'inquiry_date' => 'nullable|date',
            'utm_source' => 'nullable|string|max:100',
            'latest_visit_id' => 'nullable|uuid|exists:visits,visit_id',
            'latest_ticket_id' => 'nullable|uuid|exists:tickets,ticket_id',
            'latest_appointment_id' => 'nullable|uuid|exists:appointments,apt_id',
            'assigned_user_id' => 'nullable|uuid|exists:users,user_id',
        ]);

        $emailHash = null;
        if (isset($validatedData['email'])) {
            $emailHash = hash('sha256', $validatedData['email']);
            $validatedData['email_hash'] = $emailHash;
            unset($validatedData['email']); // email은 저장하지 않고 hash만 저장
        }

        // utm_source만 있고 연결할 기존 Visit이 없으면 새 Visit을 생성해 채널 정보를 보존
        if (isset($validatedData['utm_source']) && !isset($validatedData['latest_visit_id'])) {
            $visit = Visit::create([
                'utm_source' => $validatedData['utm_source'],
                'channel_category' => ChannelCategoryHelper::getCategoryFromUtmSource($validatedData['utm_source']),
                'first_seen_at' => now(),
            ]);
            $validatedData['latest_visit_id'] = $visit->visit_id;
        }
        unset($validatedData['utm_source']); // leads 테이블엔 해당 컬럼이 없음 (visits를 통해서만 보관)

        // 중복 리드 탐색 (전화번호/이메일이 둘 다 없으면 비교 기준이 없으므로 무조건 신규 생성)
        $hasPhone = !empty($validatedData['primary_phone'] ?? null);
        $existingLead = null;
        if ($hasPhone || $emailHash) {
            $existingLead = Lead::where(function ($query) use ($validatedData, $emailHash, $hasPhone) {
                if ($hasPhone) {
                    $query->where('primary_phone', $validatedData['primary_phone']);
                }
                if ($emailHash) {
                    // 전화번호가 없거나, 전화번호와 이메일 해시가 모두 일치하는 경우
                    $query->orWhere('email_hash', $emailHash);
                }
            })
            ->first();
        }

        // 리드 스코어 초기화 (기존 스코어가 있다면 가져오고, 없다면 0)
        $leadScore = $existingLead ? ($existingLead->score ?? 0) : 0;

        // 방문 정보를 기반으로 스코어 업데이트
        if (isset($validatedData['latest_visit_id'])) {
            $visit = Visit::find($validatedData['latest_visit_id']);
            if ($visit) {
                $leadScore = $this->calculateLeadScore($visit, $leadScore);
            }
        }

        // 요청된 스코어가 있다면 기존 스코어와 비교하여 높은 값 사용
        if (isset($validatedData['score'])) {
            $leadScore = max($leadScore, $validatedData['score']);
        }
        $validatedData['score'] = $leadScore; // 최종 스코어 반영


        if ($existingLead) {
            // 기존 리드 업데이트 (병합)
            $updateData = [
                // 새로운 방문 ID가 있다면 업데이트 (예: 최신 방문 정보로)
                'source_visit_id' => $validatedData['latest_visit_id'] ?? $existingLead->source_visit_id,
                // 동의 플래그 병합 (배열 병합)
                'consent_flags' => array_unique(array_merge($existingLead->consent_flags ?? [], $validatedData['consent_flags'] ?? [])),
                // 메모 추가
                'memo' => ($existingLead->memo ?? '') . (isset($validatedData['memo']) ? "\n---\n" . $validatedData['memo'] : ''),
                // 스코어 업데이트
                'score' => $leadScore, // 계산된 최종 스코어 적용
                // 상태는 그대로 유지하거나, 비즈니스 로직에 따라 변경 가능 (예: 'new'가 아니면 유지)
                // 'status' => $existingLead->status,
            ];

            $existingLead->update($updateData);
            return response()->json($existingLead, 200); // 200 OK for update
        } else {
            // 새로운 리드 생성
            $validatedData['lead_id'] = (string) Str::uuid(); // UUID 수동 생성
            $validatedData['source_visit_id'] = $validatedData['latest_visit_id'] ?? null; // 채널 귀속을 위해 반드시 설정

            if (isset($validatedData['assigned_user_id'])) {
                $user = User::where('user_id', $validatedData['assigned_user_id'])->first();
                if (!$user) {
                    return response()->json(['message' => 'Assigned user not found.'], 404);
                }
            }

            $lead = Lead::create($validatedData);
            return response()->json($lead, 201); // 201 Created for new resource
        }
    }

    /**
     * 모든 리드 목록을 조회합니다。
     * 페이지네이션, 검색, 정렬 및 필터링을 지원합니다。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Lead::query()
            ->with([
                'sourceVisit',
                'assignee',
                'tickets' => function ($query) {
                    $query->latest('created_at')->take(1);
                },
            ]);

        // 검색 (name, primary_phone, email)
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('primary_phone', 'like', '%' . $searchTerm . '%');
            });
        }

        // 필터링
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('channel')) {
            // LeadChannel과의 관계를 통해 필터링 (구현 시 필요)
            // 현재 Lead 모델에 직접 channel 필드가 없으므로, LeadChannel 관계를 통해 필터링 필요
            // 예: $query->whereHas('channels', function ($q) use ($request) {
            //          $q->where('channel_name', $request->input('channel'));
            //      });
            // 지금은 임시로 무시하거나, 직접 필터링할 필드가 있다면 사용
        }
        if ($request->has('assigned_user_id')) {
            $query->where('assigned_user_id', $request->input('assigned_user_id'));
        }
        if ($request->has('min_score')) {
            $query->where('score', '>=', $request->input('min_score'));
        }
        if ($request->has('max_score')) {
            $query->where('score', '<=', $request->input('max_score'));
        }
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date') . ' 23:59:59'); // 날짜 범위의 마지막까지 포함
        }

        // 정렬
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->input('per_page', 10);
        $leads = $query->paginate($perPage);

        // 필요한 정보들을 리드 객체에 추가
        $leads->getCollection()->transform(function ($lead) {
            $lead->utm_source = $lead->sourceVisit->utm_source ?? '';
            $lead->utm_campaign = $lead->sourceVisit->utm_campaign ?? '';
            $lead->assignee_name = $lead->assignee->name ?? '미배정';
            // [SLA 기능 비활성화 2026-06-22]
            // $lead->sla_status = $lead->tickets->isNotEmpty() ? ($lead->tickets->first()->sla_status ?? '-') : '-';

            // 상태 기반 카운팅 시스템
            // 상담완료(contacted): ticket = 1
            // 예약완료(scheduled): ticket = 1, booking = 1
            // 계약완료(converted): ticket = 1, booking = 1, revenue = (실제 매출)
            $status = $lead->status;

            if ($status === 'contacted') {
                $lead->tickets_count = 1;
                $lead->appointments_count = 0;
                $lead->revenue = 0;
            } elseif ($status === 'scheduled') {
                $lead->tickets_count = 1;
                $lead->appointments_count = 1;
                $lead->revenue = 0;
            } elseif ($status === 'converted') {
                $lead->tickets_count = 1;
                $lead->appointments_count = 1;
                // revenue는 Appointment 테이블의 total_revenue에서 가져옴
                $appointment = \App\Models\Appointment::where('lead_id', $lead->lead_id)->first();
                $lead->revenue = $appointment ? $appointment->total_revenue : 0;
            } else {
                // 신규, 보류, 거절 등 다른 상태
                $lead->tickets_count = 0;
                $lead->appointments_count = 0;
                $lead->revenue = 0;
            }

            // last_contact_at은 현재 Lead 모델에 직접 없으므로, 필요에 따라 Visit, Ticket, Appointment 등에서 가져와야 함.
            // 여기서는 임시로 created_at을 사용하거나, 실제 last_contact_at 필드가 있다면 사용.
            $lead->last_contact_at = $lead->created_at->format('Y-m-d H:i'); // 예시
            return $lead;
        });

        return response()->json($leads);
    }

    /**
     * 지정된 리드를 조회합니다。
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        return response()->json($lead);
    }

    /**
     * 지정된 리드를 업데이트합니다。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'primary_phone' => 'nullable|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'status' => ['sometimes', Rule::in(['new', 'contacted', 'scheduled', 'converted', 'pending', 'rejected'])],
            'score' => 'nullable|integer|min:0|max:100',
            'memo' => 'nullable|string',
            'inquiry_date' => 'nullable|date',
            'utm_source' => 'nullable|string|max:255',
            'latest_visit_id' => 'nullable|uuid|exists:visits,visit_id',
            'latest_ticket_id' => 'nullable|uuid|exists:tickets,ticket_id',
            'latest_appointment_id' => 'nullable|uuid|exists:appointments,apt_id',
            'assigned_user_id' => 'nullable|uuid|exists:users,user_id',
        ]);

        if (isset($validatedData['email'])) {
            $validatedData['email_hash'] = hash('sha256', $validatedData['email']);
            unset($validatedData['email']);
        }

        // utm_source 변경 시 연결된 Visit 업데이트
        if (array_key_exists('utm_source', $validatedData)) {
            $utmSource = $validatedData['utm_source'];
            unset($validatedData['utm_source']);

            if ($lead->sourceVisit) {
                $lead->sourceVisit->update([
                    'utm_source' => $utmSource,
                    'channel_category' => ChannelCategoryHelper::getCategoryFromUtmSource($utmSource),
                ]);
            } else {
                // Visit 없으면 새로 생성 후 연결
                $visit = \App\Models\Visit::create([
                    'utm_source' => $utmSource,
                    'channel_category' => ChannelCategoryHelper::getCategoryFromUtmSource($utmSource),
                    'first_seen_at' => now(),
                ]);
                $validatedData['source_visit_id'] = $visit->visit_id;
            }
        }

        $lead->update($validatedData);

        $lead->utm_source = $lead->fresh()->sourceVisit->utm_source ?? '';

        return response()->json($lead, 200);
    }

    /**
     * 지정된 리드를 삭제합니다。
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully.'], 200);
    }

    /**
     * 방문 정보와 현재 스코어를 기반으로 리드 스코어를 계산합니다.
     *
     * @param \App\Models\Visit $visit
     * @param int $currentScore
     * @return int
     */
    private function calculateLeadScore(Visit $visit, int $currentScore): int
    {
        $score = $currentScore;

        // 채널 가중치 (예시: 특정 UTM 소스에 높은 점수)
        switch ($visit->utm_source) {
            case 'naver':
                $score += 10;
                break;
            case 'google':
                $score += 8;
                break;
            case 'meta':
                $score += 5;
                break;
            default:
                $score += 3;
                break;
        }

        // 랜딩 페이지 가중치 (예시: 특정 랜딩 경로에 높은 점수)
        if (Str::contains($visit->landing_path, ['/premium-service', '/promotion'])) {
            $score += 15;
        } elseif (Str::contains($visit->landing_path, ['/contact', '/inquiry'])) {
            $score += 10;
        }

        // 업무 시간 외 문의 가중치 (예시: 오후 6시 ~ 오전 9시)
        $visitTime = \Carbon\Carbon::parse($visit->first_seen_at)->hour;
        if ($visitTime >= 18 || $visitTime < 9) {
            $score += 7;
        }

        // 스코어 상한선 설정
        return min($score, 100);
    }
}