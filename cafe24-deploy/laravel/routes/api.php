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
});