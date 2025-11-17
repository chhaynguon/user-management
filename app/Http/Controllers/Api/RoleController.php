<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // List all Roles
    public function index()
    {
        $role = Role::all();
        return response()->json($role);
    }

    // Show a single Roles
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    // Create new Roles
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:roles,code',
            'name' => 'required',
        ]);

        $role = Role::create($request->only('code','name','description'));
        return response()->json($role, 201);
    }

    // Update Roles
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:roles,code,' . $role->id,
            'name' => 'required',
        ]);

        $role->update($request->only('code','name','description'));
        return response()->json($role);
    }

    // Delete Roles
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Roles deleted successfully']);
    }
}
