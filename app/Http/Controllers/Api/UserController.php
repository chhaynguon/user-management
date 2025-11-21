<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['groups', 'roles.permissions', 'fnctions.permissions', 'permissions'])->get();
        return response()->json($users);
    }


    public function show($id)
    {
        $user = User::with(['groups', 'roles.permissions', 'fnctions.permissions', 'permissions'])->findOrFail($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);
        return response()->json($user, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'group_code' => 'array',
            'role_code' => 'array',
            'fnction_code' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('group_code')) {
            $user->groups()->sync($request->group_code);
        }

        if ($request->filled('role_code')) {
            $user->roles()->sync($request->role_code);
        }

        if ($request->filled('fnction_code')) {

            foreach ($request->fnction_code as $fnctionCode) {
                // Example: attach USER.NEW and USER.VIEW for this function
                $permissions = DB::table('fnction_has_permissions')
                    ->where('fnction_code', $fnctionCode)
                    ->get();

                foreach ($permissions as $perm) {
                    DB::table('user_has_permissions')->insert([
                        'user_id' => $user->id,
                        'fnction_code'  => $perm->fnction_code,
                        'permission_code' => $perm->permission_code,
                        'fnc_perm_code'  => $perm->fnc_perm_code,
                    ]);
                }
            }
        }

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',
            'group_code' => 'array',
            'role_code' => 'array',
            'fnction_code' => 'array',
        ]);

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->only(['name', 'email', 'password']));

        // Sync relationships
        $user->groups()->sync($request->group_code ?? []);
        $user->roles()->sync($request->role_code ?? []);

        if ($request->filled('fnction_code')) {
            // Delete existing function permissions first
            DB::table('user_has_permissions')->where('user_id', $user->id)->delete();

            foreach ($request->fnction_code as $fnctionCode) {
                $permissions = DB::table('fnction_has_permissions')
                    ->where('fnction_code', $fnctionCode)
                    ->get();

                foreach ($permissions as $perm) {
                    DB::table('user_has_permissions')->insert([
                        'user_id' => $user->id,
                        'fnction_code' => $perm->fnction_code,
                        'permission_code' => $perm->permission_code,
                        'fnc_perm_code' => $perm->fnc_perm_code,
                    ]);
                }
            }
        }

        return response()->json(
            User::with(['groups', 'roles.permissions', 'fnctions.permissions', 'permissions'])
                ->find($user->id)
        );
    }


    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'Profile updated successfully.', 'user' => $user]);
    }
}
