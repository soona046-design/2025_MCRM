<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // UUID 생성을 위해 추가
use App\Models\Appointment; // Appointment 모델 사용
use App\Models\Lead; // Lead 모델 사용 (lead_id 유효성 검사)
use App\Models\User; // User 모델 사용 (doctor_id 유효성 검사)

class AppointmentController extends Controller
{
    /**
     * 새로운 예약을 생성합니다.
     */
    public function store(Request $request)
    {
        // 유효성 검사
        $validatedData = $request->validate([
            'leadId' => 'required|uuid|exists:leads,lead_id',
            'clinicId' => 'nullable|string|max:255', // 지점 ID
            'doctorId' => 'nullable|uuid|exists:users,user_id', // 담당 의사/상담자
            'slotAt' => 'required|date', // 예약 슬롯 시간
            'status' => 'nullable|string|in:booked,noshow,done,cancelled',
        ]);

        // Lead 존재 여부 확인 (exists 룰로 이미 확인되지만, 명시적으로 처리)
        $lead = Lead::where('lead_id', $validatedData['leadId'])->first();
        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $aptId = Str::uuid(); // UUID 생성

        $appointment = Appointment::create([
            'apt_id' => $aptId,
            'lead_id' => $validatedData['leadId'],
            'clinic_id' => $validatedData['clinicId'] ?? null,
            'doctor_id' => $validatedData['doctorId'] ?? null,
            'slot_at' => \Carbon\Carbon::parse($validatedData['slotAt']),
            'status' => $validatedData['status'] ?? 'booked', // 기본값 'booked'
            'reminder_sent' => false, // 초기값 false
        ]);

        return response()->json(['aptId' => $appointment->apt_id], 201);
    }

    /**
     * 특정 예약의 상세 정보를 조회합니다.
     */
    public function show(string $aptId)
    {
        $appointment = Appointment::where('apt_id', $aptId)->with(['lead', 'doctor', 'clinicVisits'])->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        return response()->json($appointment);
    }

    /**
     * 특정 리드의 모든 예약을 조회합니다.
     */
    public function indexByLead(string $leadId)
    {
        $lead = Lead::where('lead_id', $leadId)->first();
        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $appointments = Appointment::where('lead_id', $leadId)->orderBy('slot_at', 'desc')->get();

        return response()->json($appointments);
    }

    /**
     * 예약 상태를 업데이트합니다.
     */
    public function updateStatus(Request $request, string $aptId)
    {
        $appointment = Appointment::where('apt_id', $aptId)->first();
        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found.'], 404);
        }

        $validatedData = $request->validate([
            'status' => 'required|string|in:booked,noshow,done,cancelled',
            'reminder_sent' => 'nullable|boolean',
        ]);

        $appointment->update([
            'status' => $validatedData['status'],
            'reminder_sent' => $validatedData['reminder_sent'] ?? $appointment->reminder_sent,
        ]);

        return response()->json(['message' => 'Appointment status updated successfully.', 'aptId' => $appointment->apt_id], 200);
    }

    /**
     * 특정 기간 동안 특정 의사/지점의 예약 가능한 슬롯을 조회합니다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots(Request $request)
    {
        $validatedData = $request->validate([
            'clinicId' => 'required|string|max:255',
            'doctorId' => 'nullable|uuid|exists:users,user_id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $clinicId = $validatedData['clinicId'];
        $doctorId = $validatedData['doctorId'] ?? null;
        $startTime = \Carbon\Carbon::parse($validatedData['start_time']);
        $endTime = \Carbon\Carbon::parse($validatedData['end_time']);

        // 특정 기간 동안의 기존 예약 조회
        $bookedSlots = Appointment::where('clinic_id', $clinicId)
            ->when($doctorId, function ($query, $doctorId) {
                return $query->where('doctor_id', $doctorId);
            })
            ->whereBetween('slot_at', [$startTime, $endTime])
            ->whereIn('status', ['booked', 'done']) // 예약되었거나 완료된 슬롯만 고려
            ->pluck('slot_at')
            ->map(fn ($slot) => \Carbon\Carbon::parse($slot)->format('Y-m-d H:i')) // 시간 형식 통일
            ->toArray();

        // 가상의 전체 슬롯 생성 (예: 30분 단위)
        $allSlots = [];
        $currentSlot = $startTime->copy();
        while ($currentSlot->lessThan($endTime)) {
            $allSlots[] = $currentSlot->format('Y-m-d H:i');
            $currentSlot->addMinutes(30); // 30분 단위 슬롯
        }

        // 예약되지 않은 슬롯 필터링
        $availableSlots = array_diff($allSlots, $bookedSlots);

        return response()->json(array_values($availableSlots)); // 배열 인덱스 재정렬
    }

    /**
     * 모든 예약을 조회합니다.
     */
    public function index(Request $request)
    {
        $query = Appointment::query()->with(['lead', 'doctor']);

        // 날짜 필터링
        if ($request->has('start_date')) {
            $query->whereDate('slot_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date')) {
            $query->whereDate('slot_at', '<=', $request->input('end_date'));
        }

        // TODO: 지점, 의사, 상태 필터 추가

        $appointments = $query->orderBy('slot_at', 'asc')->get()->map(function ($appointment) {
            return [
                'apt_id' => $appointment->apt_id,
                'lead_id' => $appointment->lead_id,
                'lead_name' => $appointment->lead->name ?? 'N/A',
                'clinic_id' => $appointment->clinic_id,
                'doctor_id' => $appointment->doctor_id,
                'doctor_name' => $appointment->doctor->name ?? '미정',
                'slot_at' => $appointment->slot_at->toISOString(),
                'status' => $appointment->status,
                'reminder_sent' => (bool) $appointment->reminder_sent,
            ];
        });

        return response()->json($appointments);
    }
}