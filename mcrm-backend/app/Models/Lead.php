<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Visit; // Visit 모델 추가
use App\Models\User; // User 모델 추가
use App\Models\Ticket; // Ticket 모델 추가

class Lead extends Model
{
    use HasFactory;
    use \App\Traits\Auditable;

    protected $primaryKey = 'lead_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'lead_id',
        'primary_phone',
        'secondary_phone',
        'email_hash',
        'name',
        'birth_date',
        'gender',
        'address',
        'city',
        'consent_flags',
        'source_visit_id',
        'status',
        'score',
        'memo',
        'latest_visit_id',
        'latest_ticket_id',
        'latest_appointment_id',
        'assigned_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'consent_flags' => 'array',
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    // TODO: 중복 리드 병합을 위한 헬퍼 메서드 추가 필요
    // 예: public static function findDuplicate(array $identifiers) { ... }

    // TODO: primary_phone, email_hash 필드에 대한 암호화/마스킹 처리 필요
    // accessor/mutator 또는 별도의 암호화 서비스 사용 고려

    // 방문(Visit)과의 관계: Lead는 하나의 Source Visit을 가집니다.
    public function sourceVisit()
    {
        return $this->belongsTo(Visit::class, 'source_visit_id', 'visit_id');
    }

    // 담당자(User)와의 관계: Lead는 한 명의 담당자를 가질 수 있습니다.
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'user_id');
    }

    // 티켓(Ticket)과의 관계: Lead는 여러 개의 티켓을 가질 수 있습니다.
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'lead_id', 'lead_id');
    }
}