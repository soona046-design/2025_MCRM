<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'campaign_code',
        'date',
        'impressions',
        'clicks',
        'cost',
    ];

    protected $casts = [
        'date' => 'date', // date 필드를 Carbon 객체로 캐스팅 (날짜만)
    ];
}