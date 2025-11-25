<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\CostImport; // CostImport 모델 사용

class AdWebhookController extends Controller
{
    public function handleWebhook($platform, Request $request)
    {
        Log::info("Received webhook for platform: {$platform}", $request->all());

        $validatedData = [];

        switch (strtolower($platform)) {
            case 'naver':
                $validatedData = $request->validate([
                    'campaign_code' => 'required|string',
                    'date' => 'required|date',
                    'impressions' => 'required|integer',
                    'clicks' => 'required|integer',
                    'cost' => 'required|numeric',
                ]);
                break;
            case 'google':
                $validatedData = $request->validate([
                    'campaignId' => 'required|string',
                    'reportDate' => 'required|date',
                    'impressionsCount' => 'required|integer',
                    'clicksCount' => 'required|integer',
                    'spend' => 'required|numeric',
                ]);
                // Google 데이터를 CostImport 스키마에 맞게 매핑
                $validatedData['campaign_code'] = $validatedData['campaignId'];
                $validatedData['date'] = $validatedData['reportDate'];
                $validatedData['impressions'] = $validatedData['impressionsCount'];
                $validatedData['clicks'] = $validatedData['clicksCount'];
                $validatedData['cost'] = $validatedData['spend'];
                break;
            case 'meta': // Facebook/Instagram Ads
                $validatedData = $request->validate([
                    'campaign_name' => 'required|string',
                    'report_date' => 'required|date',
                    'impressions' => 'required|integer',
                    'clicks' => 'required|integer',
                    'spend' => 'required|numeric',
                ]);
                // Meta 데이터를 CostImport 스키마에 맞게 매핑
                $validatedData['campaign_code'] = $validatedData['campaign_name'];
                $validatedData['date'] = $validatedData['report_date'];
                $validatedData['cost'] = $validatedData['spend'];
                break;
            default:
                return response()->json(['message' => 'Unsupported ad platform.'], 400);
        }

        // TODO: 중복 데이터 방지를 위해 고유 제약 조건에 따라 upsert (업데이트 또는 생성)
        CostImport::updateOrCreate(
            [
                'platform' => strtolower($platform),
                'campaign_code' => $validatedData['campaign_code'],
                'date' => $validatedData['date'],
            ],
            [
                'impressions' => $validatedData['impressions'],
                'clicks' => $validatedData['clicks'],
                'cost' => $validatedData['cost'],
            ]
        );

        return response()->json(['message' => 'Ad cost data received and processed successfully.'], 200);
    }
}