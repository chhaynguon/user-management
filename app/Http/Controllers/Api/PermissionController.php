<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // List all Permissions
    public function index()
    {
        $permission = Permission::all();
        return response()->json($permission);
    }

    // Show a single Permission
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    // Create new Permission
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:permissions,code',
            'name' => 'required',
        ]);

        $permission = Permission::create($request->only('code','name','description'));
        return response()->json($permission, 201);
    }

    // Update Permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:permissions,code,' . $permission->id,
            'name' => 'required',
        ]);

        $permission->update($request->only('code','name','description'));
        return response()->json($permission);
    }

    // Delete Permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
