<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성을 위해 추가
use App\Models\Ticket; // Ticket 모델을 사용하기 위해 추가
use App\Models\Lead;   // Lead 모델을 사용하기 위해 추가 (lead_id 유효성 검사)
use App\Models\User;   // User 모델을 사용하기 위해 추가 (assignee_id 유효성 검사)

class TicketController extends Controller
{
    /**
     * 새로운 상담 티켓을 생성합니다.
     */
    public function store(Request $request, string $leadId)
    {
        // 유효성 검사
        $validatedData = $request->validate([
            'assignee_id' => 'nullable|uuid|exists:users,user_id',
            'state' => 'nullable|string|in:신규,진행,보류,완료', // 명세서 상태 값
            'priority' => 'nullable|string|in:긴급,높음,일반,낮음', // 명세서 우선순위 값
            'tags' => 'nullable|array', // 태그 배열
            'notes' => 'nullable|string',
            'last_contact_at' => 'nullable|date',
        ]);

        // lead_id의 유효성 검사 (Lead 모델 존재 여부)
        if (!Lead::where('lead_id', $leadId)->exists()) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $ticketId = Str::uuid(); // UUID 생성

        $ticket = Ticket::create([
            'ticket_id' => $ticketId,
            'lead_id' => $leadId,
            'assignee_id' => $validatedData['assignee_id'] ?? null,
            'state' => $validatedData['state'] ?? '신규',
            'priority' => $validatedData['priority'] ?? '일반',
            'tags' => $validatedData['tags'] ?? null,
            'notes' => $validatedData['notes'] ?? null,
            'last_contact_at' => $validatedData['last_contact_at'] ?? now(),
        ]);

        return response()->json(['ticketId' => $ticket->ticket_id], 201);
    }

    /**
     * 특정 상담 티켓의 상태 또는 담당자를 업데이트합니다.
     */
    public function update(Request $request, string $ticketId)
    {
        // 유효성 검사
        $validatedData = $request->validate([
            'assignee_id' => 'nullable|uuid|exists:users,user_id',
            'state' => 'nullable|string|in:신규,진행,보류,완료', // 명세서 상태 값
            'priority' => 'nullable|string|in:긴급,높음,일반,낮음', // 명세서 우선순위 값
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
            'last_contact_at' => 'nullable|date',
        ]);

        $ticket = Ticket::where('ticket_id', $ticketId)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        $ticket->update($validatedData);

        return response()->json(['message' => 'Ticket updated successfully.'], 200);
    }

    /**
     * 특정 티켓의 상세 정보를 조회합니다.
     */
    public function show(string $ticketId)
    {
        $ticket = Ticket::with(['lead', 'assignee', 'communications'])->where('ticket_id', $ticketId)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found.'], 404);
        }

        return response()->json($ticket, 200);
    }
}