<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CommunicationController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\AgentDashboardController;
use App\Http\Controllers\Api\ChannelPivotController;
use App\Http\Controllers\Api\ChannelManagementController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MarketingStatsController;
use App\Http\Controllers\Api\ChannelTreatmentMatrixController;
use App\Http\Controllers\Api\MarketingInsightController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 인증 관련 라우트
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');

// 방문 수집 라우트 (인증 불필요)
Route::post('/collect/visit', [VisitController::class, 'store']);

// 개발용 - 대시보드 임시 인증 제거
Route::get('/dashboards/channel-pivot', [ChannelPivotController::class, 'index']);

// 개발용 - 채널-진료 매트릭스 임시 인증 제거
Route::get('/channel-treatment-matrix', [ChannelTreatmentMatrixController::class, 'index']);
Route::get('/channel-treatment-matrix/treatment-types', [ChannelTreatmentMatrixController::class, 'getTreatmentTypes']);
Route::get('/channel-treatment-matrix/channel-categories', [ChannelTreatmentMatrixController::class, 'getChannelCategories']);

// 인증이 필요한 라우트들
Route::middleware('auth:sanctum')->group(function () {
    // 리드 관리
    Route::apiResource('leads', LeadController::class);

    // 티켓 관리
    Route::apiResource('tickets', TicketController::class);
    Route::get('/leads/{leadId}/tickets', [TicketController::class, 'indexByLead']);
    Route::post('/leads/{leadId}/tickets', [TicketController::class, 'store']);

    // 방문 관리
    Route::apiResource('visits', VisitController::class);

    // 예약 관리
    Route::apiResource('appointments', AppointmentController::class);

    // 사용자 관리
    Route::apiResource('users', UserController::class);

    // 커뮤니케이션 관리
    Route::apiResource('communications', CommunicationController::class);
    Route::get('/tickets/{ticketId}/communications', [CommunicationController::class, 'indexByTicket']);
    Route::post('/tickets/{ticketId}/communications', [CommunicationController::class, 'store']);

    // 대시보드
    Route::get('/dashboards/funnel', [AgentDashboardController::class, 'funnel']);
    Route::get('/dashboards/agent-performance', [AgentDashboardController::class, 'agentPerformance']);

    // 마케팅 통계 (광고 성과)
    Route::get('/marketing-stats', [MarketingStatsController::class, 'index']);
    Route::get('/marketing-stats/summary', [MarketingStatsController::class, 'summary']);
    Route::get('/marketing-stats/{platform}', [MarketingStatsController::class, 'show']);

    // 내보내기
    Route::get('/exports/leads', [ExportController::class, 'leads']);
    Route::get('/exports/tickets', [ExportController::class, 'tickets']);

    // 감사 로그
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    // 알림
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // 채널 관리
    Route::prefix('channel-management')->group(function () {
        Route::get('/categories', [ChannelManagementController::class, 'getCategories']);
        Route::get('/mappings', [ChannelManagementController::class, 'getMappings']);
        Route::post('/mappings', [ChannelManagementController::class, 'createMapping']);
        Route::put('/mappings/{id}', [ChannelManagementController::class, 'updateMapping']);
        Route::delete('/mappings/{id}', [ChannelManagementController::class, 'deleteMapping']);
        Route::patch('/mappings/{id}/toggle', [ChannelManagementController::class, 'toggleMapping']);
    });

    // 채널-진료 매트릭스 관리 (인증 필요한 기능만)
    Route::prefix('channel-treatment-matrix')->group(function () {
        Route::post('/', [ChannelTreatmentMatrixController::class, 'store']); // 수동 입력
        Route::delete('/{id}', [ChannelTreatmentMatrixController::class, 'destroy']); // 삭제
        Route::post('/auto-collect', [ChannelTreatmentMatrixController::class, 'autoCollect']); // 자동 집계
    });

    // 마케팅 인사이트 (AI 분석)
    Route::prefix('marketing-insights')->group(function () {
        Route::get('/', [MarketingInsightController::class, 'index']); // 인사이트 목록
        Route::get('/{id}', [MarketingInsightController::class, 'show']); // 특정 인사이트 조회
        Route::post('/generate', [MarketingInsightController::class, 'generate']); // AI 분석 실행
        Route::patch('/{id}/publish', [MarketingInsightController::class, 'togglePublish']); // 공개/비공개 전환
        Route::delete('/{id}', [MarketingInsightController::class, 'destroy']); // 삭제
    });
});