<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelCategory;
use App\Models\ChannelTreatmentRecord;
use App\Models\TreatmentType;
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\VisitsClinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChannelTreatmentMatrixController extends Controller
{
    /**
     * 매트릭스 데이터 조회 (날짜 범위별)
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'input_type' => 'nullable|in:manual,auto,all',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $inputType = $request->input('input_type', 'all');

        $query = ChannelTreatmentRecord::with(['channelCategory', 'treatmentType', 'creator'])
            ->dateRange($startDate, $endDate);

        if ($inputType !== 'all') {
            $query->byInputType($inputType);
        }

        $records = $query->orderBy('record_date', 'desc')->get();

        // 매트릭스 형태로 변환
        $matrix = $this->buildMatrix($records);

        return response()->json([
            'records' => $records,
            'matrix' => $matrix,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    /**
     * 진료 유형 마스터 조회
     */
    public function getTreatmentTypes()
    {
        $types = TreatmentType::active()->ordered()->get();
        return response()->json($types);
    }

    /**
     * 채널 카테고리 조회
     */
    public function getChannelCategories()
    {
        $categories = ChannelCategory::active()->ordered()->get();
        return response()->json($categories);
    }

    /**
     * 수동 입력 데이터 생성/업데이트
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'record_date' => 'required|date',
            'channel_category_id' => 'required|exists:channel_categories,id',
            'treatment_type_id' => 'required|exists:treatment_types,id',
            'count' => 'required|integer|min:0',
            'revenue' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 동일한 날짜, 채널, 진료 유형이 있으면 업데이트, 없으면 생성
        $record = ChannelTreatmentRecord::updateOrCreate(
            [
                'record_date' => $request->record_date,
                'channel_category_id' => $request->channel_category_id,
                'treatment_type_id' => $request->treatment_type_id,
            ],
            [
                'count' => $request->count,
                'revenue' => $request->revenue,
                'notes' => $request->notes,
                'input_type' => 'manual',
                'created_by' => auth()->user()->user_id ?? null,
            ]
        );

        return response()->json([
            'message' => 'Record saved successfully',
            'record' => $record->load(['channelCategory', 'treatmentType']),
        ], 201);
    }

    /**
     * 특정 레코드 삭제
     */
    public function destroy($id)
    {
        $record = ChannelTreatmentRecord::findOrFail($id);

        // 자동 수집 데이터는 삭제 불가
        if ($record->input_type === 'auto') {
            return response()->json([
                'message' => 'Auto-collected records cannot be deleted'
            ], 403);
        }

        $record->delete();

        return response()->json([
            'message' => 'Record deleted successfully'
        ]);
    }

    /**
     * 자동 집계 데이터 생성
     * 기존 visits -> leads -> appointments -> visits_clinics 데이터를 분석
     */
    public function autoCollect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        DB::beginTransaction();
        try {
            // 기존 자동 수집 데이터 삭제 (해당 기간)
            ChannelTreatmentRecord::auto()
                ->dateRange($startDate, $endDate)
                ->delete();

            // visits_clinics에서 실제 내원 데이터 집계
            $data = DB::table('visits_clinics as vc')
                ->join('appointments as a', 'vc.apt_id', '=', 'a.apt_id')
                ->join('leads as l', 'a.lead_id', '=', 'l.lead_id')
                ->join('visits as v', 'l.source_visit_id', '=', 'v.visit_id')
                ->leftJoin('channel_category_mappings as ccm', 'v.utm_source', '=', 'ccm.utm_source')
                ->leftJoin('channel_categories as cc', 'ccm.category_id', '=', 'cc.id')
                ->whereBetween(DB::raw('DATE(a.slot_at)'), [$startDate, $endDate])
                ->whereNotNull('vc.procedure_code')
                ->whereNotNull('cc.id')
                ->select(
                    DB::raw('DATE(a.slot_at) as record_date'),
                    'cc.id as channel_category_id',
                    'vc.procedure_code',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(vc.charge_amount) as revenue')
                )
                ->groupBy('record_date', 'channel_category_id', 'vc.procedure_code')
                ->get();

            // procedure_code를 treatment_type_id로 매핑하고 저장
            foreach ($data as $row) {
                // procedure_code를 treatment_type code로 간주
                $treatmentType = TreatmentType::where('code', $row->procedure_code)->first();

                if ($treatmentType) {
                    ChannelTreatmentRecord::create([
                        'record_date' => $row->record_date,
                        'channel_category_id' => $row->channel_category_id,
                        'treatment_type_id' => $treatmentType->id,
                        'count' => $row->count,
                        'revenue' => $row->revenue,
                        'input_type' => 'auto',
                        'created_by' => auth()->user()->user_id ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Auto-collection completed successfully',
                'records_created' => $data->count(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Auto-collection failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 매트릭스 형태로 데이터 변환
     */
    private function buildMatrix($records)
    {
        $matrix = [];

        foreach ($records as $record) {
            $channelName = $record->channelCategory->name ?? 'Unknown';
            $treatmentName = $record->treatmentType->name ?? 'Unknown';

            if (!isset($matrix[$channelName])) {
                $matrix[$channelName] = [];
            }

            if (!isset($matrix[$channelName][$treatmentName])) {
                $matrix[$channelName][$treatmentName] = [
                    'count' => 0,
                    'revenue' => 0,
                ];
            }

            $matrix[$channelName][$treatmentName]['count'] += $record->count;
            $matrix[$channelName][$treatmentName]['revenue'] += floatval($record->revenue);
        }

        return $matrix;
    }
}
