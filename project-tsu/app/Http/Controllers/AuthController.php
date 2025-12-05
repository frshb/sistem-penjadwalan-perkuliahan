<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login User
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak dapat dibuat'
            ], 500);
        }

        $user = Auth::user();
        $roles = $user->roles()->pluck('nama_role'); // ambil role user

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $user,
            'roles' => $roles,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Ambil user yang sedang login
     */
    public function me()
    {
        $user = Auth::user();
        $roles = $user->roles()->pluck('nama_role');

        return response()->json([
            'success' => true,
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Logout dan hapus token
     */
    public function logout()
    {
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        return response()->json([
            'success' => true,
            'token' => Auth::refresh()
        ]);
    }
}
