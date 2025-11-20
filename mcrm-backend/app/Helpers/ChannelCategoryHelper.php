<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class ChannelCategoryHelper
{
    /**
     * UTM source로부터 채널 카테고리 결정 (데이터베이스 기반)
     */
    public static function getCategoryFromUtmSource(?string $utmSource): ?string
    {
        if (!$utmSource) {
            return null;
        }

        // 데이터베이스에서 매핑 조회
        $mapping = DB::table('channel_category_mappings as m')
            ->join('channel_categories as c', 'm.category_id', '=', 'c.id')
            ->where('m.active', true)
            ->where('c.active', true)
            ->orderBy('m.priority')
            ->orderBy('m.id')
            ->get();

        foreach ($mapping as $map) {
            // 대소문자 무시하고 부분 일치 확인
            if (stripos($utmSource, $map->utm_source) !== false) {
                return $map->code;
            }
        }

        // 매핑 데이터 없으면 규칙 기반 분류 사용
        return self::getCategoryByRules($utmSource);
    }

    /**
     * 규칙 기반 채널 카테고리 분류 (폴백)
     */
    private static function getCategoryByRules(?string $utmSource): ?string
    {
        if (!$utmSource) {
            return null;
        }

        $utmSource = strtolower($utmSource);

        // 온라인 채널
        $onlineChannels = [
            'naver', '네이버',
            'google', 'google ads',
            'facebook', 'facebook ads', 'meta',
            'instagram', 'kakao', 'tiktok',
            'youtube', '유튜브',
            'gdn', 'display',
        ];

        // 오프라인 채널
        $offlineChannels = [
            '전단지', '현수막', '지인추천',
            '거리홍보', '이벤트', '지나가다',
            '간판', '오프라인',
            'walk-in', 'flyer', 'banner',
        ];

        // DB 채널
        $dbChannels = [
            '기존고객', '재방문', 'crm',
            '휴면고객', 'reactivation', 'existing', 'db',
        ];

        foreach ($onlineChannels as $channel) {
            if (stripos($utmSource, $channel) !== false) {
                return 'online';
            }
        }

        foreach ($offlineChannels as $channel) {
            if (stripos($utmSource, $channel) !== false) {
                return 'offline';
            }
        }

        foreach ($dbChannels as $channel) {
            if (stripos($utmSource, $channel) !== false) {
                return 'db';
            }
        }

        // 기본값: 온라인으로 분류
        return 'online';
    }

    /**
     * 카테고리 한글명 반환
     */
    public static function getCategoryName(string $category): string
    {
        return match ($category) {
            'online' => '온라인',
            'offline' => '오프라인',
            'db' => 'DB',
            default => '알 수 없음',
        };
    }

    /**
     * 카테고리 색상 반환
     */
    public static function getCategoryColor(string $category): string
    {
        return match ($category) {
            'online' => '#2196F3',  // 파란색
            'offline' => '#FF9800', // 주황색
            'db' => '#4CAF50',      // 녹색
            default => '#9E9E9E',   // 회색
        };
    }

    /**
     * 모든 카테고리 조회
     */
    public static function getAllCategories(): array
    {
        return DB::table('channel_categories')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * 카테고리별 매핑 조회
     */
    public static function getMappingsByCategory(string $categoryCode): array
    {
        return DB::table('channel_category_mappings as m')
            ->join('channel_categories as c', 'm.category_id', '=', 'c.id')
            ->where('c.code', $categoryCode)
            ->where('m.active', true)
            ->orderBy('m.priority')
            ->select('m.utm_source', 'm.display_name')
            ->get()
            ->toArray();
    }
}

