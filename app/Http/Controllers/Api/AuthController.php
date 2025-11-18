<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // register (optional)
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        // Load roles & permissions
        $user->load(['groups.roles.permissions']);
        $roles = $user->roles()->pluck('code')->toArray();
        $permissions = $user->permissions()->pluck('code')->toArray();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
            ],
            'token' => $token
        ], 201);
    }

    // login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // delete old tokens (optional)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load roles & permissions
        $user->load(['groups.roles.permissions']);
        $roles = $user->roles()->pluck('code')->toArray();
        $permissions = $user->permissions()->pluck('code')->toArray();

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        ]);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    // current authenticated user
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->load(['groups.roles.permissions']);
        $roles = $user->roles()->pluck('code')->toArray();
        $permissions = $user->permissions()->pluck('code')->toArray();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\ValidationException;

// class AuthController extends Controller
// {
//     public function register(Request $request)
//     {
//         $data = $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email',
//             'password' => 'required|string|min:6|confirmed',
//         ]);

//         $user = User::create([
//             'name' => $data['name'],
//             'email' => $data['email'],
//             'password' => Hash::make($data['password']),
//             'role' => $request->input('role', 'user'),
//         ]);

//         $token = $user->createToken('api-token')->plainTextToken;

//         return response()->json(['user' => $user, 'token' => $token], 201);
//     }

//     public function login(Request $request)
//     {
//         $data = $request->validate([
//             'email' => 'required|email',
//             'password' => 'required|string',
//         ]);

//         $user = User::where('email', $data['email'])->first();

//         if (!$user || !Hash::check($data['password'], $user->password)) {
//             throw ValidationException::withMessages([
//                 'email' => ['The provided credentials are incorrect.'],
//             ]);
//         }
//         $user->tokens()->delete();

//         $token = $user->createToken('auth_token')->plainTextToken;
//         return response()->json(['token' => $token, 'user' => $user]);
//     }

//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()->delete();

//         return response()->json(['message' => 'Logged out']);
//     }

//     public function me(Request $request)
//     {
//         $user = $request->user();

//         if (!$user) {
//             return response()->json(['message' => 'Unauthenticated'], 401);
//         }

//         return response()->json($user);
//     }
// } 