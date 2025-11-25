<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// 메인 페이지
Route::get('/', function () {
    return response()->json(['message' => 'Insight M-CRM API Server', 'status' => 'running']);
});

// 로그인 페이지
Route::get('/login', function () {
    return response()->json(['message' => 'Login page', 'url' => '/login']);
});

// API 테스트
Route::get('/api', function () {
    return response()->json(['message' => 'API is working!', 'timestamp' => now()]);
});

// 대시보드
Route::get('/dashboards', function () {
    return response()->json(['message' => 'Dashboards page']);
});

// 리드
Route::get('/leads', function () {
    return response()->json(['message' => 'Leads page']);
});