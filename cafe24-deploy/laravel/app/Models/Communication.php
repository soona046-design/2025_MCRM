<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가

class Communication extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 추가

    protected $primaryKey = 'comm_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'comm_id',
        'ticket_id',
        'type',
        'direction',
        'content',
        'meta',
        'at',
    ];

    protected $casts = [
        'meta' => 'array', // JSON 필드를 배열로 자동 캐스팅
        'at' => 'datetime', // 날짜/시간 필드 캐스팅
    ];

    // Ticket과의 관계 정의 (하나의 커뮤니케이션은 하나의 티켓에 속함)
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}