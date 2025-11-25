<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가

class Appointment extends Model
{
    use HasFactory, HasUuids;
    use \App\Traits\Auditable; // HasUuids 트레이트 추가

    protected $primaryKey = 'apt_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'apt_id',
        'lead_id',
        'clinic_id',
        'doctor_id',
        'slot_at',
        'status',
        'total_revenue',
        'reminder_sent',
        'rebooking_suggested_at',
    ];

    protected $casts = [
        'slot_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'rebooking_suggested_at' => 'datetime',
    ];

    // Lead와의 관계 정의 (하나의 예약은 하나의 리드에 속함)
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'lead_id');
    }

    // Doctor (담당 의사/상담자)와의 관계 정의 (하나의 예약은 하나의 사용자에게 할당됨)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    // VisitsClinic과의 관계 정의 (하나의 예약은 여러 방문 기록을 가질 수 있음)
    public function clinicVisits()
    {
        return $this->hasMany(VisitsClinic::class, 'apt_id', 'apt_id');
    }
}