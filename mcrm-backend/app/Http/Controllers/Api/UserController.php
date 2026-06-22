<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $users = User::paginate((int) $request->input('per_page', 15));
        
        if ($request->boolean('mask_sensitive', true)) {
            $users->through(function ($user) {
                $user->email = $user->getMaskedEmail();
                $user->phone = $user->getMaskedPhone();
                return $user;
            });
        }
        
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string|max:255|unique:users,login_id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // role은 환경마다 표기가 다른(admin/super_admin/상담매니저 등) 자유 문자열 컬럼이라 화이트리스트를 두지 않음
            'role' => 'required|string|max:50',
            'clinic_id' => 'nullable|string|max:255', // users.clinic_id는 UUID가 아닌 일반 문자열 컬럼
            'phone' => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $user = User::create([
            'login_id' => $request->login_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'clinic_id' => $request->clinic_id,
            'phone' => $request->phone,
            'active' => $request->active ?? true, // 기본값 true
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        return response()->json($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();

        $request->validate([
            'login_id' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'login_id')->ignore($user->user_id, 'user_id')],
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|string|max:50',
            'clinic_id' => 'nullable|string|max:255', // users.clinic_id는 UUID가 아닌 일반 문자열 컬럼
            'phone' => 'nullable|string|max:20',
            'active' => 'sometimes|boolean',
        ]);

        $userData = $request->only(['login_id', 'name', 'email', 'role', 'clinic_id', 'phone', 'active']);
        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $user->delete();

        return response()->json(null, 204);
    }
}
