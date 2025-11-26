<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Fnction;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // List all roles
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $fnctions = Fnction::with('permissions')->get();

        $rolesData = $roles->map(function ($role) use ($fnctions) {
            $roleFnctions = $fnctions->map(function ($fnc) use ($role) {
                $fncPermissions = $role->permissions
                    ->where('pivot.fnction_code', $fnc->code)
                    ->map(fn($p) => [
                        'code' => $p->code,
                        'name' => $p->name,
                        'description' => $p->description,
                        'pivot' => [
                            'fnction_code' => $p->pivot->fnction_code,
                            'permission_code' => $p->pivot->permission_code,
                            'fnc_perm_code' => $p->pivot->fnc_perm_code,
                        ]
                    ]);

                return [
                    'code' => $fnc->code,
                    'name' => $fnc->name,
                    'description' => $fnc->description,
                    'permissions' => $fncPermissions
                ];
            });

            return [
                'code' => $role->code,
                'name' => $role->name,
                'description' => $role->description,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
                'fnctions' => $roleFnctions,
            ];
        });

        return response()->json($rolesData);
    }


    // Show single role by code with functions and permissions
    public function show($code)
    {
        $role = Role::with('permissions')->findOrFail($code);
        $fnctions = Fnction::with('permissions')->get();

        $roleFnctions = $fnctions->map(function ($fnc) use ($role) {
            $fncPermissions = $role->permissions
                ->where('pivot.fnction_code', $fnc->code)
                ->map(fn($p) => [
                    'code' => $p->code,
                    'name' => $p->name,
                    'description' => $p->description,
                    'pivot' => [
                        'fnction_code' => $p->pivot->fnction_code,
                        'permission_code' => $p->pivot->permission_code,
                        'fnc_perm_code' => $p->pivot->fnc_perm_code,
                    ]
                ]);

            return [
                'code' => $fnc->code,
                'name' => $fnc->name,
                'description' => $fnc->description,
                'permissions' => $fncPermissions
            ];
        });

        $roleData = [
            'code' => $role->code,
            'name' => $role->name,
            'description' => $role->description,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at,
            'fnctions' => $roleFnctions,
        ];

        return response()->json($roleData);
    }

    // Create new role
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:roles,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.fnction_code' => ['required', 'string', 'exists:fnctions,code'],
            'permissions.*.permission_code' => ['required', 'string', 'exists:permissions,code'],
        ]);

        $role = Role::create($request->only('code', 'name', 'description'));

        if (!empty($data['permissions'])) {
            $syncData = [];

            foreach ($data['permissions'] as $perm) {
                $fncPermCode = $perm['fnction_code'] . '.' . $perm['permission_code'];
                $syncData[$fncPermCode] = [
                    'fnction_code' => $perm['fnction_code'],
                    'permission_code' => $perm['permission_code'],
                    'fnc_perm_code' => $fncPermCode,
                ];
            }

            $role->permissions()->sync($syncData);
        }

        return response()->json($role->load('permissions'), 201);
    }

    // Update role
    public function update(Request $request, $code)
    {
        $role = Role::findOrFail($code);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', Rule::unique('roles', 'code')->ignore($role->code, 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.fnction_code' => ['required', 'string', 'exists:fnctions,code'],
            'permissions.*.permission_code' => ['required', 'string', 'exists:permissions,code'],
        ]);

        $role->update($request->only('code', 'name', 'description'));

        if (!empty($data['permissions'])) {
            $syncData = [];

            foreach ($data['permissions'] as $perm) {
                $fncPermCode = $perm['fnction_code'] . '.' . $perm['permission_code'];
                $syncData[$fncPermCode] = [
                    'fnction_code' => $perm['fnction_code'],
                    'permission_code' => $perm['permission_code'],
                    'fnc_perm_code' => $fncPermCode,
                ];
            }

            $role->permissions()->sync($syncData);
        }

        return response()->json($role->load('permissions'));
    }

    // Delete role
    public function destroy($code)
    {
        $role = Role::findOrFail($code);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
