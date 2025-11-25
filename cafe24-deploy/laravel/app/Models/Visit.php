<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Model 클래스 임포트
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가
use Illuminate\Database\Eloquent\Factories\HasFactory; // Factory 사용을 위해 추가

class Visit extends Model
{
    use HasUuids, HasFactory; // UUID 및 Factory 사용 트레이트

    protected $primaryKey = 'visit_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'visit_id',
        'client_id',
        'session_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referrer',
        'landing_path',
        'channel_category',
        'first_seen_at',
        'ip',
        'ua',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime', // first_seen_at을 Carbon 객체로 캐스팅
    ];

    // 필요한 경우 여기에 관계를 정의할 수 있습니다 (예: hasMany leads)
    // public function leads()
    // {
    //     return $this->hasMany(Lead::class, 'source_visit_id', 'visit_id');
    // }
}
