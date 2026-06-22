<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\Appointment;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * 리드 데이터를 내보냅니다.
     */
    public function exportLeads(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Lead::query()
            ->with(['tickets', 'appointments'])
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date);
            });

        $leads = $query->get()->map(function ($lead) {
            return [
                'lead_id' => $lead->lead_id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'source' => $lead->source,
                'status' => $lead->status,
                'total_tickets' => $lead->tickets->count(),
                'total_appointments' => $lead->appointments->count(),
                'created_at' => $lead->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $headers = [
            'Lead ID',
            '이름',
            '전화번호',
            '이메일',
            '유입 경로',
            '상태',
            '티켓 수',
            '예약 수',
            '생성일',
        ];

        $filename = 'leads_' . now()->format('Ymd_His') . ($request->format === 'csv' ? '.csv' : '.xlsx');
        
        if ($request->format === 'csv') {
            $path = $this->exportService->toCsv($leads, $headers, $filename);
        } else {
            $path = $this->exportService->toExcel($leads, $headers, $filename);
        }

        return response()->json([
            'message' => '내보내기가 완료되었습니다.',
            'download_url' => url("api/exports/download/" . basename($path)),
        ]);
    }

    /**
     * 티켓 데이터를 내보냅니다.
     */
    public function exportTickets(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Ticket::query()
            ->with(['lead', 'assignee'])
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date);
            });

        $tickets = $query->get()->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->ticket_id,
                'lead_name' => $ticket->lead->name,
                'assignee_name' => $ticket->assignee->name ?? '미배정',
                'state' => $ticket->state,
                'priority' => $ticket->priority,
                // [SLA 기능 비활성화 2026-06-22] 'sla_status' => $ticket->sla_status,
                'last_contact_at' => optional($ticket->last_contact_at)->format('Y-m-d H:i:s'),
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $headers = [
            'Ticket ID',
            '리드 이름',
            '담당자',
            '상태',
            '우선순위',
            // [SLA 기능 비활성화 2026-06-22] 'SLA 상태',
            '마지막 연락',
            '생성일',
        ];

        $filename = 'tickets_' . now()->format('Ymd_His') . ($request->format === 'csv' ? '.csv' : '.xlsx');
        
        if ($request->format === 'csv') {
            $path = $this->exportService->toCsv($tickets, $headers, $filename);
        } else {
            $path = $this->exportService->toExcel($tickets, $headers, $filename);
        }

        return response()->json([
            'message' => '내보내기가 완료되었습니다.',
            'download_url' => url("api/exports/download/" . basename($path)),
        ]);
    }

    /**
     * 예약 데이터를 내보냅니다.
     */
    public function exportAppointments(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,excel',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Appointment::query()
            ->with(['lead', 'doctor'])
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('slot_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('slot_at', '<=', $request->end_date);
            });

        $appointments = $query->get()->map(function ($appointment) {
            return [
                'appointment_id' => $appointment->appointment_id,
                'lead_name' => $appointment->lead->name,
                'doctor_name' => $appointment->doctor->name,
                'slot_at' => $appointment->slot_at->format('Y-m-d H:i:s'),
                'status' => $appointment->status,
                'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $headers = [
            'Appointment ID',
            '리드 이름',
            '담당의',
            '예약 시간',
            '상태',
            '생성일',
        ];

        $filename = 'appointments_' . now()->format('Ymd_His') . ($request->format === 'csv' ? '.csv' : '.xlsx');
        
        if ($request->format === 'csv') {
            $path = $this->exportService->toCsv($appointments, $headers, $filename);
        } else {
            $path = $this->exportService->toExcel($appointments, $headers, $filename);
        }

        return response()->json([
            'message' => '내보내기가 완료되었습니다.',
            'download_url' => url("api/exports/download/" . basename($path)),
        ]);
    }

    /**
     * 내보내기 파일을 다운로드합니다.
     */
    public function download(string $filename): StreamedResponse
    {
        $path = "exports/{$filename}";
        
        if (!Storage::exists($path)) {
            abort(404, '파일을 찾을 수 없습니다.');
        }

        $response = Storage::download($path);
        
        // 다운로드 후 파일 삭제 (선택사항)
        $this->exportService->deleteExportFile($path);
        
        return $response;
    }
}
