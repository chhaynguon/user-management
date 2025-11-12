<?php

namespace App\Http\Controllers\Api;

use App\Models\Permissions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // List all Permissionss
    public function index()
    {
        $Permissions = Permissions::all();
        return response()->json($Permissions);
    }

    // Show a single Permissions
    public function show($id)
    {
        $Permissions = Permissions::findOrFail($id);
        return response()->json($Permissions);
    }

    // Create new Permissions
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:Permissions,code',
            'name' => 'required',
        ]);

        $Permissions = Permissions::create($request->only('code','name','description'));
        return response()->json($Permissions, 201);
    }

    // Update Permissions
    public function update(Request $request, $id)
    {
        $Permissions = Permissions::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:Permissions,code,' . $Permissions->id,
            'name' => 'required',
        ]);

        $Permissions->update($request->only('code','name','description'));
        return response()->json($Permissions);
    }

    // Delete Permissions
    public function destroy($id)
    {
        $Permissions = Permissions::findOrFail($id);
        $Permissions->delete();
        return response()->json(['message' => 'Permissions deleted successfully']);
    }
}
