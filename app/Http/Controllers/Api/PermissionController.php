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
        return response()->json(Permission::all());
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
