<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ChannelManagementController extends Controller
{
    /**
     * 모든 카테고리 목록 조회
     */
    public function getCategories()
    {
        $categories = DB::table('channel_categories')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        return Response::json($categories);
    }

    /**
     * 모든 채널 매핑 조회
     */
    public function getMappings(Request $request)
    {
        $categoryId = $request->input('category_id');

        $query = DB::table('channel_category_mappings as m')
            ->join('channel_categories as c', 'm.category_id', '=', 'c.id')
            ->select(
                'm.id',
                'm.utm_source',
                'm.display_name',
                'm.category_id',
                'c.name as category_name',
                'c.code as category_code',
                'c.color as category_color',
                'm.priority',
                'm.active',
                'm.created_at',
                'm.updated_at'
            );

        if ($categoryId) {
            $query->where('m.category_id', $categoryId);
        }

        $mappings = $query->orderBy('c.sort_order')
            ->orderBy('m.display_name')
            ->get();

        return Response::json($mappings);
    }

    /**
     * 채널 매핑 생성
     */
    public function createMapping(Request $request)
    {
        $validated = $request->validate([
            'utm_source' => 'required|string|max:100',
            'display_name' => 'required|string|max:100',
            'category_id' => 'required|exists:channel_categories,id',
            'priority' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        // 중복 확인
        $exists = DB::table('channel_category_mappings')
            ->where('utm_source', $validated['utm_source'])
            ->where('category_id', $validated['category_id'])
            ->exists();

        if ($exists) {
            return Response::json([
                'error' => '이미 존재하는 채널 매핑입니다.'
            ], 409);
        }

        $id = DB::table('channel_category_mappings')->insertGetId([
            'utm_source' => $validated['utm_source'],
            'display_name' => $validated['display_name'],
            'category_id' => $validated['category_id'],
            'priority' => $validated['priority'] ?? 0,
            'active' => $validated['active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mapping = DB::table('channel_category_mappings as m')
            ->join('channel_categories as c', 'm.category_id', '=', 'c.id')
            ->where('m.id', $id)
            ->select(
                'm.id',
                'm.utm_source',
                'm.display_name',
                'm.category_id',
                'c.name as category_name',
                'c.code as category_code',
                'c.color as category_color',
                'm.priority',
                'm.active'
            )
            ->first();

        return Response::json($mapping, 201);
    }

    /**
     * 채널 매핑 수정
     */
    public function updateMapping(Request $request, $id)
    {
        $validated = $request->validate([
            'utm_source' => 'string|max:100',
            'display_name' => 'string|max:100',
            'category_id' => 'exists:channel_categories,id',
            'priority' => 'integer|min:0',
            'active' => 'boolean',
        ]);

        $mapping = DB::table('channel_category_mappings')->where('id', $id)->first();

        if (!$mapping) {
            return Response::json(['error' => '채널을 찾을 수 없습니다.'], 404);
        }

        $validated['updated_at'] = now();

        DB::table('channel_category_mappings')
            ->where('id', $id)
            ->update($validated);

        $updated = DB::table('channel_category_mappings as m')
            ->join('channel_categories as c', 'm.category_id', '=', 'c.id')
            ->where('m.id', $id)
            ->select(
                'm.id',
                'm.utm_source',
                'm.display_name',
                'm.category_id',
                'c.name as category_name',
                'c.code as category_code',
                'c.color as category_color',
                'm.priority',
                'm.active'
            )
            ->first();

        return Response::json($updated);
    }

    /**
     * 채널 매핑 삭제
     */
    public function deleteMapping($id)
    {
        $deleted = DB::table('channel_category_mappings')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return Response::json(['error' => '채널을 찾을 수 없습니다.'], 404);
        }

        return Response::json(['message' => '채널이 삭제되었습니다.']);
    }

    /**
     * 채널 활성화/비활성화
     */
    public function toggleMapping($id)
    {
        $mapping = DB::table('channel_category_mappings')->where('id', $id)->first();

        if (!$mapping) {
            return Response::json(['error' => '채널을 찾을 수 없습니다.'], 404);
        }

        DB::table('channel_category_mappings')
            ->where('id', $id)
            ->update([
                'active' => !$mapping->active,
                'updated_at' => now(),
            ]);

        return Response::json(['active' => !$mapping->active]);
    }
}
