<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:60',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'api')->plainTextToken,
            'user' => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}