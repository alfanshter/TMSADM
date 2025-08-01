<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    

    // Ambil semua user
    public function index()
    {
        return User::with(['creator', 'updater'])->get();
    }

    // Tambah user baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,supervisor,team_leader',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|url',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => true,
            'avatar' => $request->avatar,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($user, 201);
    }

    // Ambil detail user
    public function show(User $user)
    {
        return $user->load(['creator', 'updater']);
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'sometimes|in:admin,supervisor,team_leader',
            'phone' => 'nullable|string',
            'status' => 'boolean',
            'avatar' => 'nullable|url',
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'status', 'avatar']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $data['updated_by'] = $request->user()->id;

        $user->update($data);

        return response()->json($user);
    }

    // Hapus user
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
