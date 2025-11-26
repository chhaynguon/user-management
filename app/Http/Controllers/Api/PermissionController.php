<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    // List all permissions
    public function index()
    {
        $permissions = Permission::with('fnctions')->get();

        // Map so each permission includes fnction_code
        $permissions = $permissions->map(function ($p) {
            return [
                'code' => $p->code,
                'name' => $p->name,
                'description' => $p->description,
                'fnction_code' => $p->fnctions->pluck('code')->first() // pick first function if multiple
            ];
        });

        return response()->json($permissions);
    }

    // Get permission by code
    public function show($code)
    {
        $permission = Permission::where('code', $code)->firstOrFail();
        return response()->json($permission);
    }

    // Create permission
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:permissions,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string']
        ]);

        $permission = Permission::create($data);
        return response()->json($permission, 201);
    }

    // Update permission (by code)
    public function update(Request $request, $code)
    {
        $permission = Permission::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('permissions', 'code')->ignore($permission->code, 'code')
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string']
        ]);

        $permission->update($data);
        return response()->json($permission);
    }

    // Delete permission
    public function destroy($code)
    {
        $permission = Permission::where('code', $code)->firstOrFail();
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
