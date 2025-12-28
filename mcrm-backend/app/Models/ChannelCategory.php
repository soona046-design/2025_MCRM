<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChannelCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'color',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 이 카테고리에 대한 기록들
     */
    public function records()
    {
        return $this->hasMany(ChannelTreatmentRecord::class);
    }

    /**
     * 활성화된 카테고리만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * 정렬 순서대로 조회
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
