<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuditLogController;
use App\Models\Lead; // Lead 모델 사용

class SensitiveDataController extends Controller
{
    /**
     * 개인 정보를 마스킹하여 반환하거나, 권한이 있을 경우 마스킹 해제된 데이터를 반환합니다.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $request->validate([
            'lead_id' => 'required_without_all:phone,email|uuid|exists:leads,id',
            'phone' => 'required_without_all:lead_id,email|string',
            'email' => 'required_without_all:lead_id,phone|string|email',
            'unmask' => 'boolean',
        ]);

        $lead = null;
        if ($request->has('lead_id')) {
            $lead = \App\Models\Lead::find($request->input('lead_id'));
        } elseif ($request->has('phone')) {
            $lead = \App\Models\Lead::where('phone', $request->input('phone'))->first();
        } elseif ($request->has('email')) {
            $lead = \App\Models\Lead::where('email', $request->input('email'))->first();
        }

        if (!$lead) {
            return response()->json(['message' => 'Lead not found.'], 404);
        }

        $maskedPhone = $this->maskPhoneNumber($lead->phone);
        $maskedEmail = $this->maskEmail($lead->email);

        $responseData = [
            'lead_id' => $lead->id,
            'phone' => $maskedPhone,
            'email' => $maskedEmail,
        ];

        // AC 3. 권한 없으면 "요청하기" 표시 (프론트엔드에서 처리)

        if ($request->input('unmask') && Auth::check()) {
            // TODO: RBAC 권한 확인 로직 추가
            // 예: if (Auth::user()->can('view sensitive data')) {
            $hasPermission = true; // 임시로 항상 권한이 있다고 가정

            if ($hasPermission) {
                $responseData['phone'] = $lead->phone;
                $responseData['email'] = $lead->email;

                // AC 1. 감사 로그 남기기
                AuditLogController::record(
                    Auth::id(),
                    'unmask_sensitive_data',
                    'lead',
                    $lead->id,
                    $request->ip()
                );
            } else {
                // 권한이 없는 경우 마스킹된 데이터 반환 및 감사 로그 기록
                AuditLogController::record(
                    Auth::id(),
                    'unmask_sensitive_data_denied',
                    'lead',
                    $lead->id,
                    $request->ip()
                );
                return response()->json(['message' => 'Permission denied to unmask sensitive data.'], 403);
            }
        }

        return response()->json($responseData);
    }

    /**
     * 전화번호를 마스킹합니다 (예: 010-****-1234).
     *
     * @param string $phoneNumber
     * @return string
     */
    private function maskPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }
        return preg_replace('/(?<=\d{3}-)\d{4}(?=-\d{4})/', '****', $phoneNumber); // 010-xxxx-yyyy
    }

    /**
     * 이메일을 마스킹합니다 (예: user****@example.com).
     *
     * @param string $email
     * @return string
     */
    private function maskEmail($email)
    {
        if (empty($email)) {
            return null;
        }
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            return substr($username, 0, 4) . '****@' . $domain; // user****@example.com
        }
        return $email; // 유효하지 않은 이메일 형식은 그대로 반환
    }
}