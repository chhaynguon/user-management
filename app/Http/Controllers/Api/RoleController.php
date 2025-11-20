<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // List all roles
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    // Show single role by code
    public function show($code)
    {
        $role = Role::where('code', $code)->with('permissions')->firstOrFail();
        return response()->json($role);
    }

    // Create new role
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:100','unique:roles,code'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);

        $role = Role::create($data);
        return response()->json($role, 201);
    }

    // Update role by code
    public function update(Request $request, $code)
    {
        $role = Role::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'code' => ['required','string','max:100', Rule::unique('roles','code')->ignore($role->code, 'code')],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
        ]);

        // If code changed, we should update the model's primary key (string). Eloquent will handle it.
        $role->update($data);

        return response()->json($role);
    }

    // Delete role by code
    public function destroy($code)
    {
        $role = Role::where('code', $code)->firstOrFail();
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
