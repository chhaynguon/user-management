<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fnction;
use Illuminate\Validation\Rule;

class FnctionController extends Controller
{
    // List all functions
    public function index()
    {
        $fnctions = Fnction::with('permissions')->get();
        return response()->json($fnctions);
    }

    // Show single function by code
    public function show($code)
    {
        $fnction = Fnction::where('code', $code)->with('permissions')->firstOrFail();
        return response()->json($fnction);
    }

    // Create new function
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:100','unique:fnctions,code'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'permission_codes' => ['nullable','array'],
            'permission_codes.*' => ['string','exists:permissions,code'],
        ]);

        $fnction = Fnction::create($request->only('code','name','description'));

        if (!empty($data['permission_codes'])) {
            $fnction->permissions()->sync($data['permission_codes']);
        }

        return response()->json($fnction->load('permissions'), 201);
    }

    // Update function by code
    public function update(Request $request, $code)
    {
        $fnction = Fnction::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'code' => ['required','string','max:100', Rule::unique('fnctions','code')->ignore($fnction->code, 'code')],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'permission_codes' => ['nullable','array'],
            'permission_codes.*' => ['string','exists:permissions,code'],
        ]);

        $fnction->update($request->only('code','name','description'));

        if (array_key_exists('permission_codes', $data)) {
            $fnction->permissions()->sync($data['permission_codes'] ?? []);
        }

        return response()->json($fnction->load('permissions'));
    }

    // Delete function by code
    public function destroy($code)
    {
        $fnction = Fnction::where('code', $code)->firstOrFail();
        $fnction->delete();

        return response()->json(['message' => 'Function deleted successfully.']);
    }
}
