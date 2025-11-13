<?php

namespace App\Http\Controllers\Api;

use App\Models\Functions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FunctionController extends Controller
{
    // List all Functionss
    public function index()
    {
        $Functions = Functions::all();
        return response()->json($Functions);
    }

    // Show a single Functions
    public function show($id)
    {
        $Functions = Functions::findOrFail($id);
        return response()->json($Functions);
    }

    // Create new Functions
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:Functions,code',
            'name' => 'required',
        ]);

        $Functions = Functions::create($request->only('code','name','description'));
        return response()->json($Functions, 201);
    }

    // Update Functions
    public function update(Request $request, $id)
    {
        $Functions = Functions::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:Functions,code,' . $Functions->id,
            'name' => 'required',
        ]);

        $Functions->update($request->only('code','name','description'));
        return response()->json($Functions);
    }

    // Delete Functions
    public function destroy($id)
    {
        $Functions = Functions::findOrFail($id);
        $Functions->delete();
        return response()->json(['message' => 'Functions deleted successfully']);
    }
}
