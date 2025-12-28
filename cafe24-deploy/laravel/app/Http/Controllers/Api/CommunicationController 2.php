<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Communication;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 모든 커뮤니케이션을 조회하는 로직 (필요시 구현)
        // 예: $communications = Communication::paginate(10);
        // return response()->json($communications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|uuid|exists:leads,lead_id',
            'ticket_id' => 'required|uuid|exists:tickets,ticket_id',
            'recipient' => 'required|string|max:255',
            'message' => 'required|string',
            'channel' => ['required', 'string', Rule::in(['sms', 'kakaotalk', 'call'])],
            'template_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $communication = Communication::create([
            'ticket_id' => $request->input('ticket_id'),
            'lead_id' => $request->input('lead_id'), // lead_id 추가
            'type' => $request->input('channel'), // channel을 type으로 매핑
            'direction' => 'outbound', // ReplyPanel에서 보내는 메시지는 outbound
            'content' => $request->input('message'), // message를 content로 매핑
            'meta' => [
                'recipient' => $request->input('recipient'),
                'template_id' => $request->input('template_id'),
            ], // recipient와 template_id를 meta에 저장
            'at' => now(),
        ]);

        // 관련 Ticket의 last_contact_at 업데이트
        $ticket = Ticket::find($communication->ticket_id);
        if ($ticket) {
            $ticket->update(['last_contact_at' => now()]);
        }

        return response()->json(['commId' => $communication->comm_id], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $communication = Communication::find($id);

        if (!$communication) {
            return response()->json(['message' => 'Communication not found.'], 404);
        }

        return response()->json($communication);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 업데이트 로직 (필요시 구현)
        // 예: $communication = Communication::find($id);
        // if (!$communication) { ... }
        // $communication->update($request->validated());
        // return response()->json($communication);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // 삭제 로직 (필요시 구현)
        // 예: $communication = Communication::find($id);
        // if (!$communication) { ... }
        // $communication->delete();
        // return response()->json(['message' => 'Communication deleted successfully.']);
    }
}
