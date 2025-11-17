<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // List all Group
    public function index()
    {
        $group = Group::all();
        return response()->json($group);
    }

    // Show a single Group
    public function show($id)
    {
        $group = Group::findOrFail($id);
        return response()->json($group);
    }

    // Create new Group
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:groups,code',
            'name' => 'required',
            'description' => 'nullable'
        ]);

        $group = Group::create($request->only('code','name','description'));
        return response()->json($group, 201);
    }

    // Update Group
    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:groups,code,' . $group->id,
            'name' => 'required',
            'description' => 'nullable'
        ]);

        $group->update($request->only('code','name','description'));
        return response()->json($group);
    }

    // Delete Group
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully']);
    }
}
