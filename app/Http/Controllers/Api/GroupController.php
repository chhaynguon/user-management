<?php

namespace App\Http\Controllers\Api;

use App\Models\Groups;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // List all Groups
    public function index()
    {
        $Groups = Groups::all();
        return response()->json($Groups);
    }

    // Show a single Groups
    public function show($id)
    {
        $Groups = Groups::findOrFail($id);
        return response()->json($Groups);
    }

    // Create new Groups
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $Groups = Groups::create($request->only('code','name','description'));
        return response()->json($Groups, 201);
    }

    // Update Groups
    public function update(Request $request, $id)
    {
        $Groups = Groups::findOrFail($id);

        $request->validate([
            'name' => 'required',
        ]);

        $Groups->update($request->only('code','name','description'));
        return response()->json($Groups);
    }

    // Delete Groups
    public function destroy($id)
    {
        $Groups = Groups::findOrFail($id);
        $Groups->delete();
        return response()->json(['message' => 'Groups deleted successfully']);
    }
}
