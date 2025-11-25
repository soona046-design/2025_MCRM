<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * AdMetric Model
 * 광고 성과 지표 데이터 (플랫폼별 주차/월별 집계)
 */
class AdMetric extends Model
{
    use HasFactory;

    protected $table = 'ad_metrics';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'platform',
        'channel_type',
        'period_type',
        'period_label',
        'date_start',
        'date_end',
        'impressions',
        'clicks',
        'ctr',
        'conversions',
        'cost',
        'cpl',
        'cpa',
        'meta_json',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:3',
        'conversions' => 'integer',
        'cost' => 'integer',
        'cpl' => 'integer',
        'cpa' => 'integer',
        'meta_json' => 'array',
    ];

    /**
     * Boot method: UUID 자동 생성
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // 저장 전 CTR, CPL, CPA 자동 계산
        static::saving(function ($model) {
            $model->calculateMetrics();
        });
    }

    /**
     * 성과 지표 계산
     */
    public function calculateMetrics(): void
    {
        // CTR 계산: (클릭 / 노출) * 100
        if ($this->impressions > 0) {
            $this->ctr = round(($this->clicks / $this->impressions) * 100, 3);
        } else {
            $this->ctr = null;
        }

        // CPL 계산: 비용 / 전환
        if ($this->conversions > 0) {
            $this->cpl = (int) round($this->cost / $this->conversions);
        } else {
            $this->cpl = null;
        }

        // CPA도 동일하게 계산 (전환이 예약인 경우)
        if ($this->conversions > 0) {
            $this->cpa = (int) round($this->cost / $this->conversions);
        } else {
            $this->cpa = null;
        }
    }

    /**
     * 스코프: 플랫폼별 필터
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * 스코프: 채널 유형별 필터
     */
    public function scopeChannelType($query, string $channelType)
    {
        return $query->where('channel_type', $channelType);
    }

    /**
     * 스코프: 기간 범위 필터
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_start', [$startDate, $endDate]);
    }

    /**
     * 스코프: 주차별 데이터
     */
    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'week');
    }

    /**
     * 스코프: 월별 데이터
     */
    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'month');
    }

    /**
     * 플랫폼 레이블 (한글)
     */
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            'naver' => '네이버',
            'google' => '구글',
            'meta' => '메타',
            default => $this->platform,
        };
    }

    /**
     * 채널 유형 레이블 (한글)
     */
    public function getChannelTypeLabelAttribute(): string
    {
        return match($this->channel_type) {
            'keyword' => '키워드 광고',
            'place' => '플레이스 광고',
            'powercontent' => '파워컨텐츠 광고',
            'gdn' => 'GDN 광고',
            'youtube' => '유튜브 광고',
            'sns' => 'SNS 광고',
            default => $this->channel_type,
        };
    }
}
