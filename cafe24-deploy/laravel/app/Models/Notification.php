<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasUuids;

    protected $primaryKey = 'notification_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'ticket_id',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * 알림 타입 상수
     */
    const TYPE_SLA_WARNING = 'sla_warning';
    const TYPE_SLA_VIOLATION = 'sla_violation';
    const TYPE_TICKET_ASSIGNED = 'ticket_assigned';
    const TYPE_TICKET_UPDATED = 'ticket_updated';

    /**
     * 알림을 받는 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * 관련 티켓
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    /**
     * 알림을 읽음 처리
     */
    public function markAsRead(): void
    {
        $this->read_at = now();
        $this->save();
    }

    /**
     * 알림이 읽혔는지 확인
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
