<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserHasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware('has-permission:USER.VIEW')->only(['index']);
        // $this->middleware('has-permission:USER.NEW')->only(['store']);
    }
    public function index()
    {
        $users = User::with(['groups', 'roles.permissions', 'fnctions' => function ($q) {
            $q->withCount('permissions');
        }, 'permissions'])->orderByDesc('id')->get();
        $data = $users->map(function ($user) {
            $user->fnctions = $user->fnctions->map(function ($func) use ($user) {
                $func->selectedPermission = UserHasPermission::whereUserId($user->id)
                ->whereFnctionCode($func->code)
                ->pluck('permission_code');
                return $func;
            });
            return $user;
        });
        return response()->json($data);
    }


    public function show($id)
    {
        $user = User::with(['groups', 'roles.permissions', 'fnctions' => function ($q) {
            $q->withCount('permissions');
        }, 'permissions'])->findOrFail($id);

        // Map fnctions to include selected permissions
        $user->fnctions = $user->fnctions->map(function ($func) use ($user) {
            $func->selectedPermission = UserHasPermission::whereUserId($user->id)
                ->whereFnctionCode($func->code)
                ->pluck('permission_code');
            return $func;
        });

        return response()->json($user);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',

            'group_code' => 'array',
            'role_code' => 'array',

            'fnction_permission' => 'array',
            'fnction_permission.*.fnction_code' => 'required|string|exists:fnctions,code',
            'fnction_permission.*.permission_codes' => 'required|array',
            'fnction_permission.*.permission_codes.*' => 'string|exists:permissions,code',

        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (!empty($validated['group_code'])) {
            $user->groups()->sync($validated['group_code']);
        }

        if (!empty($validated['role_code'])) {
            $user->roles()->sync($validated['role_code']);
        }

        // Insert user permissions
        if (!empty($validated['fnction_permission'])) {
            $syncData = [];

            foreach ($validated['fnction_permission'] as $item) {
                $syncData[] = [
                    'user_id' => $user->id,
                    'fnction_code' => $item['fnction_code'],
                    'permission_code' => $item['permission_code'],
                    'fnc_perm_code' => $item['fnc_perm_code'],
                ];
            }

            DB::table('user_has_permissions')->insert($syncData);
        }

        return response()->json(
            $user->load(['groups', 'roles.permissions', 'fnctions.permissions', 'permissions'])
        );
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',

            'group_code' => 'array',
            'role_code' => 'array',

            'fnction_permission' => 'array',
            'fnction_permission.*.fnction_code' => 'required|string|exists:fnctions,code',
            'fnction_permission.*.permission_codes' => 'required|array',
            'fnction_permission.*.permission_codes.*' => 'string|exists:permissions,code',

        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('group_code')) {
            $user->groups()->sync($validated['group_code']);
        }

        if ($request->filled('role_code')) {
            $user->roles()->sync($validated['role_code']);
        }

        // Delete old permissions
        DB::table('user_has_permissions')->where('user_id', $user->id)->delete();

        // Insert new permissions
        if (!empty($validated['fnction_permission'])) {
            $syncData = [];

            foreach ($validated['fnction_permission'] as $item) {
                foreach ($item['permission_codes'] as $permCode) {
                    $syncData[] = [
                        'user_id' => $user->id,
                        'fnction_code' => $item['fnction_code'],
                        'permission_code' => $permCode,
                        'fnc_perm_code' => $item['fnction_code'] . '.' . $permCode,
                    ];
                }
            }

            DB::table('user_has_permissions')->insert($syncData);
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

    public function showWithPermissions($id)
    {
        $user = User::with([
            'roles.permissions',
            'groups.roles.permissions',
        ])->findOrFail($id);

        // Collect all permissions the user has (direct + roles + group roles)
        $allPermissions = collect();

        // Direct user permissions
        $user->permissions->each(fn($p) => $allPermissions->push([
            'fnction_code' => $p->pivot->fnction_code,
            'permission_code' => $p->code,
            'source' => 'user',
            'fnc_perm_code' => $p->pivot->fnc_perm_code,
        ]));

        // Permissions via user's roles
        $user->roles->each(function ($role) use ($allPermissions) {
            $role->permissions->each(fn($p) => $allPermissions->push([
                'fnction_code' => $p->pivot->fnction_code,
                'permission_code' => $p->code,
                'source' => 'role',
                'fnc_perm_code' => $p->pivot->fnc_perm_code,
            ]));
        });

        // Permissions via groups → roles
        $user->groups->each(function ($group) use ($allPermissions) {
            $group->roles->each(function ($role) use ($allPermissions) {
                $role->permissions->each(fn($p) => $allPermissions->push([
                    'fnction_code' => $p->pivot->fnction_code,
                    'permission_code' => $p->code,
                    'source' => 'group_role',
                    'fnc_perm_code' => $p->pivot->fnc_perm_code,
                ]));
            });
        });

        // Group permissions by function
        $fnctionMap = $allPermissions->groupBy('fnction_code');

        // Build fnctions array
        $fnctions = $fnctionMap->map(function ($perms, $fnCode) {
            $permissions = $perms->map(fn($p) => [
                'code' => $p['permission_code'],
                'selected' => true,
                'fnc_perm_code' => $p['fnc_perm_code']
            ])->unique('code')->values();

            $selectedPermission = $permissions->pluck('code')->toArray();

            return [
                'code' => $fnCode,
                'name' => $fnCode, // Replace with real name if needed
                'permissions' => $permissions,
                'selectedPermission' => $selectedPermission,
            ];
        })->values();

        // Flatten roles and group roles permissions for easy lookup if needed
        $rolePermissions = $user->roles->map(function ($role) {
            return [
                'role_code' => $role->code,
                'permissions' => $role->permissions->map(fn($p) => $p->code)
            ];
        });

        $groupRolePermissions = $user->groups->map(function ($group) {
            return [
                'group_code' => $group->code,
                'roles' => $group->roles->map(function ($role) {
                    return [
                        'role_code' => $role->code,
                        'permissions' => $role->permissions->map(fn($p) => $p->code)
                    ];
                })
            ];
        });

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'groups' => $user->groups,
            'roles' => $user->roles,
            'fnctions' => $fnctions,
            'role_permissions' => $rolePermissions,
            'group_role_permissions' => $groupRolePermissions,
        ]);
    }

    public function currentUserPermissions(Request $request)
    {
        $user = $request->user();
        return response()->json($user->allPermissions());
    }
}
