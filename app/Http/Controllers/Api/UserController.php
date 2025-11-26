<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fnction;
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
        $user = User::with(['groups', 'roles.permissions', 'permissions'])->findOrFail($id);

        // Build user's functions with only their permissions
        $fnCodes = $user->permissions->pluck('fnction_code')->unique();

        $user->fnctions = Fnction::whereIn('code', $fnCodes)
            ->get()
            ->map(function ($fn) use ($user) {
                $fn->permissions = $user->permissions
                    ->where('fnction_code', $fn->code)
                    ->map(function ($perm) {
                        return [
                            'code' => $perm->code,
                            'name' => $perm->name
                        ];
                    })
                    ->values();
                return $fn;
            });

        return response()->json($user, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'group_code' => 'array',
            'role_code' => 'array',
            'fnction_code' => 'array',
            'fnction_permission' => 'array',
            'fnction_permission.*.fnction_code' => 'required|string|exists:fnctions,code',
            'fnction_permission.*.permission_code' => 'required|string|exists:permissions,code',
            'fnction_permission.*.fnc_perm_code' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // Attach groups
        if (!empty($data['group_code'])) {
            $user->groups()->sync($data['group_code']);
        }

        // Attach roles
        if (!empty($data['role_code'])) {
            $user->roles()->sync($data['role_code']);
        }

        // Attach functions + permissions
        if (!empty($data['fnction_permission'])) {
            $syncData = [];
            foreach ($data['fnction_permission'] as $fp) {
                $syncData[] = [
                    'user_id' => $user->id,
                    'fnction_code' => $fp['fnction_code'],
                    'permission_code' => $fp['permission_code'],
                    'fnc_perm_code' => $fp['fnc_perm_code'],
                ];
            }

            // Insert into pivot table
            DB::table('user_has_permissions')->insert($syncData);
        }

        return response()->json($user->load(['groups', 'roles', 'fnctions.permissions']));
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',
            'group_code' => 'array',
            'role_code' => 'array',
            'fnction_code' => 'array',
            'fnction_permission' => 'array',
            'fnction_permission.*.fnction_code' => 'required|string|exists:fnctions,code',
            'fnction_permission.*.permission_code' => 'required|string|exists:permissions,code',
            'fnction_permission.*.fnc_perm_code' => 'required|string|max:255',
        ]);

        // Hash password if provided
        if ($request->filled('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->only(['name', 'email', 'password']));

        // Sync groups and roles
        if ($request->filled('group_code')) {
            $user->groups()->sync($request->group_code);
        }

        if ($request->filled('role_code')) {
            $user->roles()->sync($request->role_code);
        }

        // Sync function permissions
        $fnPermissions = $request->fnction_permission ?? [];
        DB::table('user_has_permissions')->where('user_id', $user->id)->delete();

        foreach ($fnPermissions as $fnctionCode => $permCodes) {
            foreach ($permCodes as $permCode) {
                DB::table('user_has_permissions')->insert([
                    'user_id' => $user->id,
                    'fnction_code' => $fnctionCode,
                    'permission_code' => $permCode,
                    'fnc_perm_code' => $fnctionCode . '.' . $permCode,
                ]);
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
