<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // UUID 사용을 위해 추가
use Laravel\Sanctum\HasApiTokens; // Sanctum API 토큰을 위해 추가

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens;
    use \App\Traits\Auditable;

    protected $primaryKey = 'user_id'; // 기본 키 설정
    protected $keyType = 'string'; // 기본 키 타입이 문자열임을 명시
    public $incrementing = false; // 기본 키가 자동 증가 정수가 아님을 명시

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean', // 추가: active 필드 캐스팅
    ];

    /**
     * 전화번호를 마스킹하여 반환합니다.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getMaskedPhone(?string $value = null): ?string
    {
        $phone = $value ?? $this->attributes['phone'] ?? null;
        if (empty($phone)) {
            return null;
        }

        return preg_replace('/(\d{3,4})(\d{4})(\d{4})/', '$1-****-$3', $phone);
    }

    /**
     * 이메일을 마스킹하여 반환합니다.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getMaskedEmail(?string $value = null): ?string
    {
        $email = $value ?? $this->attributes['email'] ?? null;
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        list($user, $domain) = explode('@', $email);
        $userMasked = substr($user, 0, 2) . str_repeat('*', strlen($user) - 2);
        
        $domainParts = explode('.', $domain);
        $domainMasked = '';
        foreach ($domainParts as $key => $part) {
            if ($key === 0) {
                $domainMasked .= substr($part, 0, 2) . str_repeat('*', strlen($part) - 2);
            } else {
                $domainMasked .= '.' . $part;
            }
        }
        return $userMasked . '@' . $domainMasked;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'login_id',
        'name',
        'email',
        'password',
        'role',
        'clinic_id',
        'phone',
        'two_fa_secret',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_fa_secret',
    ];

    // 관계 메서드들

    /**
     * 사용자가 담당하는 리드들과의 관계
     */
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_user_id', 'user_id');
    }

    /**
     * 사용자가 담당하는 티켓들과의 관계
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assignee_id', 'user_id');
    }

    /**
     * 사용자가 담당하는 예약들과의 관계
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'assigned_user_id', 'user_id');
    }

    /**
     * 사용자가 받은 알림들과의 관계
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }
}
