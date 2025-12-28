<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketingInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_period_start',
        'analysis_period_end',
        'insight_type',
        'title',
        'content',
        'recommendations',
        'confidence_score',
        'generated_by',
        'is_published',
    ];

    protected $casts = [
        'analysis_period_start' => 'date',
        'analysis_period_end' => 'date',
        'content' => 'array',
        'recommendations' => 'array',
        'confidence_score' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    /**
     * 생성자 (사용자) 관계
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    /**
     * 공개된 인사이트만 조회
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * 특정 유형의 인사이트 조회
     */
    public function scopeByType($query, $type)
    {
        return $query->where('insight_type', $type);
    }

    /**
     * 기간별 조회
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('analysis_period_start', [$startDate, $endDate])
              ->orWhereBetween('analysis_period_end', [$startDate, $endDate]);
        });
    }

    /**
     * 최신순 정렬
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
