<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TreatmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'color',
        'sort_order',
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 이 진료 유형에 대한 기록들
     */
    public function records()
    {
        return $this->hasMany(ChannelTreatmentRecord::class);
    }

    /**
     * 활성화된 진료 유형만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * 카테고리별 조회
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 정렬 순서대로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
