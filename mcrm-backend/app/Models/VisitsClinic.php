<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가

class VisitsClinic extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 추가

    protected $primaryKey = 'clinic_visit_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'clinic_visit_id',
        'apt_id',
        'emr_visit_no',
        'procedure_code',
        'charge_amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime', // 날짜/시간 필드 캐스팅
        'charge_amount' => 'decimal:2', // 소수점 두 자리까지의 decimal 캐스팅
    ];

    // Appointment와의 관계 정의 (하나의 클리닉 방문은 하나의 예약에 속함)
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'apt_id', 'apt_id');
    }
}