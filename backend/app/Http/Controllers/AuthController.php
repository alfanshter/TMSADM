<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Ambil user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek user dan password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

         // Buat token login (Sanctum)
        $token = $user->createToken('api-token')->plainTextToken;

        // Jika role admin, berikan respons khusus
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Hi, saya admin',
                'token' => $token,
                'user' => $user
            ]);
        }

        // Untuk role lainnya, berikan respons umum
         // Respons login sukses
        return response()->json([
            'status' => 1,
            'message' => $user->role === 'admin'
                ? 'Hi, saya admin'
                : 'Hi, anda login sebagai ' . $user->role,
            'data' => $user,
            'token' => $token
        ]);
}

    public function logout(Request $request)
    {

    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);

    }
}
