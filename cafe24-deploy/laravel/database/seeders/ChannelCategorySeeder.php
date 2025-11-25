<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 카테고리 데이터
        $categories = [
            ['code' => 'online', 'name' => '온라인', 'color' => '#2196F3', 'sort_order' => 1],
            ['code' => 'offline', 'name' => '오프라인', 'color' => '#FF9800', 'sort_order' => 2],
            ['code' => 'db', 'name' => 'DB', 'color' => '#4CAF50', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            DB::table('channel_categories')->insert(array_merge($category, [
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 채널 매핑 데이터
        $mappings = [
            // 온라인
            ['utm_source' => '네이버 키워드 광고', 'category_code' => 'online', 'display_name' => '네이버 키워드'],
            ['utm_source' => '네이버 플레이스 광고', 'category_code' => 'online', 'display_name' => '네이버 플레이스'],
            ['utm_source' => '네이버 파워 콘텐츠 광고', 'category_code' => 'online', 'display_name' => '네이버 파워 콘텐츠'],
            ['utm_source' => 'GDN 광고', 'category_code' => 'online', 'display_name' => 'GDN'],
            ['utm_source' => 'YOUTUBE 광고', 'category_code' => 'online', 'display_name' => 'YouTube'],
            ['utm_source' => 'naver', 'category_code' => 'online', 'display_name' => '네이버'],
            ['utm_source' => '네이버', 'category_code' => 'online', 'display_name' => '네이버'],
            ['utm_source' => 'google', 'category_code' => 'online', 'display_name' => '구글'],
            ['utm_source' => 'Google Ads', 'category_code' => 'online', 'display_name' => '구글 광고'],

            // 오프라인
            ['utm_source' => '오프라인광고', 'category_code' => 'offline', 'display_name' => '오프라인 광고'],
            ['utm_source' => '간판', 'category_code' => 'offline', 'display_name' => '간판'],
            ['utm_source' => '소개', 'category_code' => 'offline', 'display_name' => '소개'],
            ['utm_source' => '지인추천', 'category_code' => 'offline', 'display_name' => '지인 추천'],

            // DB (메타 광고)
            ['utm_source' => '메타광고', 'category_code' => 'db', 'display_name' => '메타 광고'],
            ['utm_source' => 'meta', 'category_code' => 'db', 'display_name' => 'Meta'],
            ['utm_source' => 'facebook', 'category_code' => 'db', 'display_name' => 'Facebook'],
            ['utm_source' => 'Facebook Ads', 'category_code' => 'db', 'display_name' => 'Facebook 광고'],
            ['utm_source' => 'instagram', 'category_code' => 'db', 'display_name' => 'Instagram'],
        ];

        foreach ($mappings as $mapping) {
            $category = DB::table('channel_categories')
                ->where('code', $mapping['category_code'])
                ->first();

            if ($category) {
                DB::table('channel_category_mappings')->insert([
                    'utm_source' => $mapping['utm_source'],
                    'display_name' => $mapping['display_name'],
                    'category_id' => $category->id,
                    'priority' => 0,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Channel categories and mappings seeded successfully.');
    }
}
