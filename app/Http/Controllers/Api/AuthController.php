<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponds;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponds;

    // ─────────────────────────────────────────
    // LOGIN  POST /api/auth/login
    // ─────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $employee = Employee::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens for this device (prevents token accumulation)
        $deviceName = $request->device_name ?? 'api-token';
        $employee->tokens()->where('name', $deviceName)->delete();

        $token = $employee->createToken($deviceName);

        $employee->load('hotel');

        return $this->ok([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'employee'   => [
                'id'    => $employee->id,
                'name'  => $employee->name,
                'email' => $employee->email,
                'role'  => $employee->role,
                'hotel' => [
                    'id'   => $employee->hotel->id,
                    'name' => $employee->hotel->name,
                ],
            ],
        ], 'Login successful');
    }

    // ─────────────────────────────────────────
    // LOGOUT  POST /api/auth/logout
    // ─────────────────────────────────────────

   public function logout(Request $request): JsonResponse
{
    $token = $request->user()->currentAccessToken();

    if ($token) {
        $token->delete();
    }

    return $this->ok(null, 'Logged out successfully');
}

    // ─────────────────────────────────────────
    // ME  GET /api/auth/me
    // ─────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        $employee = $request->user()->load('hotel');

        return $this->ok([
            'id'         => $employee->id,
            'name'       => $employee->name,
            'email'      => $employee->email,
            'role'       => $employee->role,
            'is_active'  => $employee->is_active,
            'hotel'      => [
                'id'      => $employee->hotel->id,
                'name'    => $employee->hotel->name,
                'address' => $employee->hotel->address,
                'phone'   => $employee->hotel->phone,
            ],
            'avatar_url' => $employee->avatar_url,
        ]);
    }
}