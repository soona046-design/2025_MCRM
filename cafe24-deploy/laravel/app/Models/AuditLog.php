<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'fields_masked',
        'old_values',
        'new_values',
        'at',
        'ip',
    ];

    protected $casts = [
        'fields_masked' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'at' => 'datetime',
    ];

    /**
     * 활동 타입 상수
     */
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_VIEWED = 'viewed';
    const ACTION_EXPORTED = 'exported';

    /**
     * 활동을 수행한 사용자
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }
}