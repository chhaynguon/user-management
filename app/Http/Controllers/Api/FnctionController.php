<?php

namespace App\Http\Controllers\Api;

use App\Models\Fnction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FnctionController extends Controller
{
    // List all Fnctions
    public function index()
    {
        $fnction = Fnction::all();
        return response()->json($fnction);
    }

    // Show a single Fnction
    public function show($id)
    {
        $fnction = Fnction::findOrFail($id);
        return response()->json($fnction);
    }

    // Create new Fnction
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:Fnctions,code',
            'name' => 'required',
        ]);

        $fnction = Fnction::create($request->only('code','name','description'));
        return response()->json($fnction, 201);
    }

    // Update Fnction
    public function update(Request $request, $id)
    {
        $fnction = Fnction::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:Fnctions,code,' . $fnction->id,
            'name' => 'required',
        ]);

        $fnction->update($request->only('code','name','description'));
        return response()->json($fnction);
    }

    // Delete Fnction
    public function destroy($id)
    {
        $fnction = Fnction::findOrFail($id);
        $fnction->delete();
        return response()->json(['message' => 'Fnction deleted successfully']);
    }
}
