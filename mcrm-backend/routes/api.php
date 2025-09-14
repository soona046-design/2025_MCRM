<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VisitController; // VisitController를 사용하기 위해 추가

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Visits Data Collection API
Route::post('/collect/visit', [VisitController::class, 'collectVisit']);

// ... existing code ...

use App\Http\Controllers\Api\LeadController; // LeadController를 사용하기 위해 추가

// Leads API
Route::post('/leads', [LeadController::class, 'store']);