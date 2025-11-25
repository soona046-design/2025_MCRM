<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the trait.
     */
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::logAuditEvent($model, AuditLog::ACTION_CREATED);
        });

        static::updated(function ($model) {
            static::logAuditEvent($model, AuditLog::ACTION_UPDATED);
        });

        static::deleted(function ($model) {
            static::logAuditEvent($model, AuditLog::ACTION_DELETED);
        });
    }

    /**
     * 감사 로그를 기록합니다.
     */
    protected static function logAuditEvent($model, string $action)
    {
        $user = Auth::user();

        $data = [
            'actor_id' => $user?->user_id,
            'action' => $action,
            'target_type' => class_basename($model),
            'target_id' => $model->getKey(),
            'ip' => Request::ip(),
            'at' => now(),
        ];

        if ($action === AuditLog::ACTION_UPDATED) {
            $data['old_values'] = $model->getOriginal();
            $data['new_values'] = $model->getAttributes();
        } elseif ($action === AuditLog::ACTION_CREATED) {
            $data['new_values'] = $model->getAttributes();
        } elseif ($action === AuditLog::ACTION_DELETED) {
            $data['old_values'] = $model->getAttributes();
        }

        AuditLog::create($data);
    }

    /**
     * 조회 활동을 기록합니다.
     */
    public function logView()
    {
        $user = Auth::user();

        AuditLog::create([
            'actor_id' => $user?->user_id,
            'action' => AuditLog::ACTION_VIEWED,
            'target_type' => class_basename($this),
            'target_id' => $this->getKey(),
            'ip' => Request::ip(),
            'at' => now(),
        ]);
    }

    /**
     * 내보내기 활동을 기록합니다.
     */
    public static function logExport(string $type, array $filters = [])
    {
        $user = Auth::user();

        AuditLog::create([
            'actor_id' => $user?->user_id,
            'action' => AuditLog::ACTION_EXPORTED,
            'target_type' => $type,
            'fields_masked' => ['filters' => $filters],
            'ip' => Request::ip(),
            'at' => now(),
        ]);
    }
}
