<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가

class Lead extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 추가

    protected $primaryKey = 'lead_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'lead_id',
        'primary_phone',
        'email_hash',
        'name',
        'consent_flags',
        'source_visit_id',
        'status',
        'score',
    ];

    protected $casts = [
        'consent_flags' => 'array', // JSON 필드를 배열로 자동 캐스팅
    ];

    // Visit과의 관계 정의 (source_visit_id를 통해 visits 테이블 참조)
    public function visit()
    {
        return $this->belongsTo(Visit::class, 'source_visit_id', 'visit_id');
    }

    // LeadChannel과의 관계 정의 (하나의 리드는 여러 채널을 가질 수 있음)
    public function channels()
    {
        return $this->hasMany(LeadChannel::class, 'lead_id', 'lead_id');
    }

    // Ticket과의 관계 정의 (하나의 리드는 여러 티켓을 가질 수 있음)
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'lead_id', 'lead_id');
    }

    // Appointment와의 관계 정의 (하나의 리드는 여러 예약을 가질 수 있음)
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'lead_id', 'lead_id');
    }
}