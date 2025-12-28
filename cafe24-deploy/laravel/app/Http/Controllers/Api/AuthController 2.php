<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 사용자 로그인 및 토큰 발급
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('login_id', $request->login_id)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login_id' => ['아이디 또는 비밀번호가 일치하지 않습니다.'],
            ]);
        }

        // 기존 토큰 삭제 (선택사항)
        if ($request->boolean('revoke_existing', true)) {
            $user->tokens()->where('name', $request->device_name)->delete();
        }

        $token = $user->createToken($request->device_name);

        $response = [
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'login_id' => $user->login_id,
            ],
        ];

        \Log::info('Login successful', ['user_id' => $user->user_id, 'response' => $response]);

        return response()->json($response);
    }

    /**
     * 현재 사용자 정보 조회
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->getMaskedEmail(),
                'role' => $user->role,
                'phone' => $user->getMaskedPhone(),
                'clinic_id' => $user->clinic_id,
                'active' => $user->active,
            ],
        ]);
    }

    /**
     * 로그아웃 (현재 기기)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '로그아웃되었습니다.']);
    }

    /**
     * 모든 기기에서 로그아웃
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => '모든 기기에서 로그아웃되었습니다.']);
    }

    /**
     * 비밀번호 변경
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['현재 비밀번호가 일치하지 않습니다.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // 선택적으로 다른 기기의 토큰을 삭제할 수 있습니다
        if ($request->boolean('revoke_others', false)) {
            $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();
        }

        return response()->json(['message' => '비밀번호가 변경되었습니다.']);
    }
}
