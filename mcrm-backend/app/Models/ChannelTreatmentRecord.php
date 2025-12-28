<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChannelTreatmentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_date',
        'channel_category_id',
        'treatment_type_id',
        'count',
        'revenue',
        'notes',
        'input_type',
        'created_by',
    ];

    protected $casts = [
        'record_date' => 'date',
        'count' => 'integer',
        'revenue' => 'decimal:2',
    ];

    /**
     * 채널 카테고리 관계
     */
    public function channelCategory()
    {
        return $this->belongsTo(\App\Models\ChannelCategory::class, 'channel_category_id');
    }

    /**
     * 진료 유형 관계
     */
    public function treatmentType()
    {
        return $this->belongsTo(TreatmentType::class);
    }

    /**
     * 입력자 (사용자) 관계
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * 날짜 범위로 조회
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * 특정 채널로 조회
     */
    public function scopeByChannel($query, $channelCategoryId)
    {
        return $query->where('channel_category_id', $channelCategoryId);
    }

    /**
     * 특정 진료 유형으로 조회
     */
    public function scopeByTreatment($query, $treatmentTypeId)
    {
        return $query->where('treatment_type_id', $treatmentTypeId);
    }

    /**
     * 입력 방식으로 조회
     */
    public function scopeByInputType($query, $inputType)
    {
        return $query->where('input_type', $inputType);
    }

    /**
     * 수동 입력 데이터만
     */
    public function scopeManual($query)
    {
        return $query->where('input_type', 'manual');
    }

    /**
     * 자동 수집 데이터만
     */
    public function scopeAuto($query)
    {
        return $query->where('input_type', 'auto');
    }
}
