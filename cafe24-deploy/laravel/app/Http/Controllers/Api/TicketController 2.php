<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성을 위해 추가
use App\Models\Ticket; // Ticket 모델 사용
use App\Models\Lead; // Lead 모델 사용 (lead_id 유효성 검사)
use App\Models\User; // User 모델 사용 (assignee_id 유효성 검사)
use Carbon\Carbon; // Carbon 사용 (last_contact_at 처리)
use Illuminate\Support\Facades\Log; // Log 파사드 추가

class TicketController extends Controller
{
    /**
     * 모든 상담 티켓 목록을 조회합니다.
     * 페이지네이션, 검색, 정렬 및 필터링을 지원합니다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        Log::info('TicketController index method called.', $request->all()); // 요청 파라미터 로깅

        $query = Ticket::query()->with(['lead', 'assignee']); // Lead 및 Assignee 관계 Eager 로딩

        // 검색 (title, notes 등)
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('notes', 'like', '%' . $searchTerm . '%'); // 현재 notes만 검색
                // 필요에 따라 title 등 추가 검색 필드 구현
            });
        }

        // 필터링
        if ($request->has('state')) {
            $query->where('state', $request->input('state'));
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->input('assignee_id'));
        }
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->input('lead_id'));
        }
        // SLA 관련 필터링 (예: sla_due_at이 임박한 티켓)
        if ($request->has('sla_status')) {
            $query->where('sla_status', $request->input('sla_status'));
        }
        if ($request->boolean('last_contact_at_null')) {
            $query->whereNull('last_contact_at');
        }


        // 쿼리 빌더의 최종 SQL 및 바인딩 값 로깅 (실제로 실행되기 직전)
        Log::info('Ticket query SQL:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // 정렬
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $perPage = $request->input('per_page', 10);
        $tickets = $query->paginate($perPage);

        Log::info('Fetched tickets count:', ['count' => $tickets->total()]); // 조회된 티켓 개수 로깅

        // 필요한 정보들을 티켓 객체에 추가 (프론트엔드 요구사항에 따라)
        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->assignee_name = $ticket->assignee->name ?? '미배정';
            $ticket->lead_name = $ticket->lead->name ?? 'N/A';
            // SLA 타이머 계산
            if ($ticket->sla_due_at) {
                $now = Carbon::now();
                $dueAt = Carbon::parse($ticket->sla_due_at);
                $ticket->sla_timer = [
                    'remaining' => $now->diffInMinutes($dueAt, false),
                    'formatted' => $dueAt->diffForHumans(['parts' => 2]),
                    'status' => $now->gt($dueAt) ? 'violated' : ($now->diffInMinutes($dueAt) <= 30 ? 'warning' : 'normal')
                ];
            }
            return $ticket;
        });

        return response()->json($tickets);
    }

    /**
     * 새로운 상담 티켓을 생성합니다.
     */
    public function store(Request $request, string $leadId)
    {
        // Lead 존재 여부 확인
        $lead = Lead::where('lead_id', $leadId)->first();
        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        // 유효성 검사
        $validatedData = $request->validate([
            'assignee_id' => 'nullable|uuid|exists:users,user_id',
            'state' => 'nullable|string|in:신규,진행,보류,완료',
            'priority' => 'nullable|string|in:긴급,높음,일반,낮음',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'last_contact_at' => 'nullable|date',
        ]);

        $ticketId = Str::uuid(); // UUID 생성

        $ticket = Ticket::create([
            'ticket_id' => $ticketId,
            'lead_id' => $leadId,
            'assignee_id' => $validatedData['assignee_id'] ?? null,
            'state' => $validatedData['state'] ?? '신규',
            'priority' => $validatedData['priority'] ?? '일반',
            'tags' => $validatedData['tags'] ?? [],
            'notes' => $validatedData['notes'] ?? null,
            'last_contact_at' => $validatedData['last_contact_at'] ? \Carbon\Carbon::parse($validatedData['last_contact_at']) : now(),
        ]);

        return response()->json(['ticketId' => $ticket->ticket_id], 201);
    }

    /**
     * 기존 상담 티켓을 업데이트합니다 (상태, 담당자 등).
     */
    public function update(Request $request, string $ticketId)
    {
        // Ticket 존재 여부 확인
        $ticket = Ticket::where('ticket_id', $ticketId)->first();
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        // 유효성 검사
        $validatedData = $request->validate([
            'assignee_id' => 'nullable|uuid|exists:users,user_id',
            'state' => 'nullable|string|in:신규,진행,보류,완료',
            'priority' => 'nullable|string|in:긴급,높음,일반,낮음',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'last_contact_at' => 'nullable|date',
        ]);

        $ticket->update([
            'assignee_id' => $validatedData['assignee_id'] ?? $ticket->assignee_id,
            'state' => $validatedData['state'] ?? $ticket->state,
            'priority' => $validatedData['priority'] ?? $ticket->priority,
            'tags' => $validatedData['tags'] ?? $ticket->tags,
            'notes' => $validatedData['notes'] ?? $ticket->notes,
            'last_contact_at' => $validatedData['last_contact_at'] ? \Carbon\Carbon::parse($validatedData['last_contact_at']) : $ticket->last_contact_at,
        ]);

        return response()->json(['message' => 'Ticket updated successfully.', 'ticketId' => $ticket->ticket_id], 200);
    }

    /**
     * 특정 리드의 모든 티켓을 조회합니다.
     */
    public function indexByLead(string $leadId)
    {
        $lead = Lead::where('lead_id', $leadId)->first();
        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $tickets = Ticket::where('lead_id', $leadId)->orderBy('created_at', 'desc')->get();

        return response()->json($tickets);
    }

    /**
     * 특정 티켓의 상세 정보를 조회합니다.
     */
    public function show(string $ticketId)
    {
        $ticket = Ticket::where('ticket_id', $ticketId)->with(['lead', 'assignee', 'communications'])->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        return response()->json($ticket);
    }
}