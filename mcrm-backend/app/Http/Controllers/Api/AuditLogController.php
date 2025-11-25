<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * 감사 로그 목록을 조회합니다.
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|uuid|exists:users,user_id',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|string',
        ]);

        $query = AuditLog::with('user')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->filled('model_type'), function ($query) use ($request) {
                $query->where('model_type', $request->model_type);
            })
            ->when($request->filled('model_id'), function ($query) use ($request) {
                $query->where('model_id', $request->model_id);
            })
            ->orderBy('created_at', 'desc');

        $logs = $query->paginate($request->input('per_page', 15));

        return response()->json($logs);
    }

    /**
     * 감사 로그를 CSV로 내보냅니다.
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|uuid|exists:users,user_id',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
        ]);

        $query = AuditLog::with('user')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('created_at', '<=', $request->end_date);
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->filled('model_type'), function ($query) use ($request) {
                $query->where('model_type', $request->model_type);
            })
            ->orderBy('created_at', 'desc');

        $logs = $query->get()->map(function ($log) {
            return [
                'audit_log_id' => $log->audit_log_id,
                'user_name' => $log->user->name ?? '알 수 없는 사용자',
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });

        $headers = [
            'Audit Log ID',
            '사용자',
            '작업',
            '대상 유형',
            '대상 ID',
            '설명',
            'IP 주소',
            '생성일',
        ];

        $filename = 'audit_logs_' . now()->format('Ymd_His') . '.csv';
        $path = $this->exportService->toCsv($logs, $headers, $filename);

        // 내보내기 활동 기록
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => AuditLog::ACTION_EXPORTED,
            'model_type' => 'AuditLog',
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => '내보내기가 완료되었습니다.',
            'download_url' => url("api/exports/download/" . basename($path)),
        ]);
    }
}