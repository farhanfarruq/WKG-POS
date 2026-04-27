<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use HasApiResponse;

    protected function transformUser(User $user, bool $includePermissions = false): array
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'roles' => $user->getRoleNames()->values()->all(),
        ];

        if ($includePermissions) {
            $payload['permissions'] = $user->getAllPermissions()->pluck('name')->values()->all();
        }

        return $payload;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Email atau password salah.', null, 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return $this->errorResponse('Akun tidak aktif.', null, 403);
        }

        $token = $user->createToken(
            'pos-token',
            $user->getPermissionNames()->toArray()
        )->plainTextToken;

        return $this->successResponse([
            'user'  => $this->transformUser($user),
            'roles' => $user->getRoleNames()->values()->all(),
            'token' => $token,
        ], 'Login berhasil.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles');

        return $this->successResponse($this->transformUser($user, includePermissions: true));
    }
}
