<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가

class Ticket extends Model
{
    use HasFactory, HasUuids; // HasUuids 트레이트 추가

    protected $primaryKey = 'ticket_id'; // 기본 키 설정
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시

    protected $fillable = [
        'ticket_id',
        'lead_id',
        'assignee_id',
        'state',
        'priority',
        'tags',
        'notes',
        'last_contact_at',
    ];

    protected $casts = [
        'tags' => 'array', // JSON 필드를 배열로 자동 캐스팅
        'last_contact_at' => 'datetime', // 날짜/시간 필드 캐스팅
    ];

    // Lead와의 관계 정의 (하나의 티켓은 하나의 리드에 속함)
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'lead_id');
    }

    // Assignee (담당자)와의 관계 정의 (하나의 티켓은 하나의 사용자에게 할당됨)
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id', 'user_id');
    }

    // Communication과의 관계 정의 (하나의 티켓은 여러 커뮤니케이션을 가질 수 있음)
    public function communications()
    {
        return $this->hasMany(Communication::class, 'ticket_id', 'ticket_id');
    }
}