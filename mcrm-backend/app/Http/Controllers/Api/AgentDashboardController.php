<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // 사용자 모델
use App\Models\Ticket; // 티켓 모델
use App\Models\Appointment; // 예약 모델
use App\Models\VisitsClinic; // 클리닉 방문 모델 (매출 연동)
use Carbon\Carbon; // 날짜/시간 처리
use Illuminate\Support\Facades\Log; // 로그 패키지

class AgentDashboardController extends Controller
{
    /**
     * 상담자별 성과 데이터를 반환합니다.
     */
    public function agentPerformance(Request $request)
    {
        // 유효성 검사 (필터 파라미터)
        $request->validate([
            'start_date' => 'nullable|date', // Y-m-d 형식
            'end_date' => 'nullable|date|after_or_equal:start_date', // Y-m-d 형식
            'clinic_id' => 'nullable|string', // 특정 지점 ID
            'agent_id' => 'nullable|uuid|exists:users,user_id', // 특정 상담자 ID
        ]);

        $query = User::query();

        // 지점 필터
        if ($clinicId = $request->input('clinic_id')) {
            $query->where('clinic_id', $clinicId);
        }

        // 특정 상담자 필터
        if ($agentId = $request->input('agent_id')) {
            $query->where('user_id', $agentId);
        }

        // 기본적으로 상담/지점관리 역할을 가진 사용자만 대상으로
        // role 컬럼이 자유 문자열이라 환경마다 표기가 다름(한글/영문 혼용) — 둘 다 매칭
        $query->whereIn('role', ['상담매니저', '지점관리자', 'counselor', 'branch_manager']);


        $agents = $query->get();

        $results = $agents->map(function ($agent) use ($request) {
            // 기간 필터링 로직
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

            // 티켓 조회
            $tickets = Ticket::where('assignee_id', $agent->user_id)
                             ->with(['communications' => function ($query) {
                                 $query->whereNotNull('at')->orderBy('at');
                             }])
                             ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                 $query->whereBetween('created_at', [$startDate, $endDate]);
                             })
                             ->get();

            // 예약 조회 (booked_by 정책 반영 - doctor_id 사용)
            $appointments = Appointment::where('doctor_id', $agent->user_id)
                                       ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                           $query->whereBetween('slot_at', [$startDate, $endDate]);
                                       })
                                       ->get();

            // 내원 및 매출 조회
            $clinicVisits = VisitsClinic::whereHas('appointment', function ($query) use ($agent, $startDate, $endDate) {
                                            $query->where('doctor_id', $agent->user_id)
                                                  ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                                      $query->whereBetween('slot_at', [$startDate, $endDate]);
                                                  });
                                        })
                                        ->whereNotNull('paid_at') // 결제 완료된 내원만
                                        ->get();

            // 지표 계산
            $totalTickets = $tickets->count();
            $totalAppointments = $appointments->count();
            $totalClinicVisits = $clinicVisits->count();
            $totalRevenue = $clinicVisits->sum('charge_amount');

            // 평균 응답 속도 계산
            $totalResponseTime = 0;
            $respondedTicketsCount = 0;
            foreach ($tickets as $ticket) {
                if ($ticket->communications->isNotEmpty()) {
                    $firstCommunication = $ticket->communications->first();
                    $responseTimeInMinutes = $ticket->created_at->diffInMinutes($firstCommunication->at);
                    $totalResponseTime += $responseTimeInMinutes;
                    $respondedTicketsCount++;
                }
            }
            $averageResponseTime = ($respondedTicketsCount > 0) ? round($totalResponseTime / $respondedTicketsCount, 2) : 0;

            // SLA 위반율 계산
            $violatedTicketsCount = $tickets->where('sla_status', 'violated')->count();
            $slaViolationRate = ($totalTickets > 0) ? round(($violatedTicketsCount / $totalTickets) * 100, 2) : 0;

            // 예약 전환율 (예약 수 / 티켓 수)
            $appointmentConversionRate = ($totalTickets > 0) ? round(($totalAppointments / $totalTickets) * 100, 2) : 0;

            // 내원 전환율 (내원 수 / 예약 수)
            $clinicVisitConversionRate = ($totalAppointments > 0) ? round(($totalClinicVisits / $totalAppointments) * 100, 2) : 0;

            return [
                'agent_id' => $agent->user_id,
                'agent_name' => $agent->name,
                'clinic_id' => $agent->clinic_id,
                'tickets_count' => $totalTickets,
                'average_response_time' => $averageResponseTime,
                'appointment_conversion_count' => $totalAppointments,
                'clinic_visit_count' => $totalClinicVisits,
                'revenue' => $totalRevenue,
                'appointment_conversion_rate' => $appointmentConversionRate,
                'clinic_visit_conversion_rate' => $clinicVisitConversionRate,
                'sla_violation_rate' => $slaViolationRate,
            ];
        });

        Log::info('Agent Performance Results', ['results' => $results->toArray()]);
        return response()->json($results);
    }
}