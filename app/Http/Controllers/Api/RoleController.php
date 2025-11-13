<?php

namespace App\Http\Controllers\Api;

use App\Models\Roles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // List all Roles
    public function index()
    {
        $Roles = Roles::all();
        return response()->json($Roles);
    }

    // Show a single Roles
    public function show($id)
    {
        $Roles = Roles::findOrFail($id);
        return response()->json($Roles);
    }

    // Create new Roles
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $Roles = Roles::create($request->only('code','name','description'));
        return response()->json($Roles, 201);
    }

    // Update Roles
    public function update(Request $request, $id)
    {
        $Roles = Roles::findOrFail($id);

        $request->validate([
            'name' => 'required',
        ]);

        $Roles->update($request->only('code','name','description'));
        return response()->json($Roles);
    }

    // Delete Roles
    public function destroy($id)
    {
        $Roles = Roles::findOrFail($id);
        $Roles->delete();
        return response()->json(['message' => 'Roles deleted successfully']);
    }
}
