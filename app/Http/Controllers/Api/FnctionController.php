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
            'code' => ['required', 'string', 'max:100', 'unique:fnctions,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permission_codes' => ['nullable', 'array'],
            'permission_codes.*' => ['string', 'exists:permissions,code'],
        ]);

        $fnction = Fnction::create($request->only('code', 'name', 'description'));

        if (!empty($data['permission_codes'])) {
            $syncData = [];
            foreach ($data['permission_codes'] as $permCode) {
                $syncData[$permCode] = [
                    'fnc_perm_code' => $fnction->code . '.' . $permCode
                ];
            }
            $fnction->permissions()->sync($syncData);
        }



        return response()->json($fnction->load('permissions'), 201);
    }

    // Update function by code
    public function update(Request $request, $code)
    {
        $fnction = Fnction::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', Rule::unique('fnctions', 'code')->ignore($fnction->code, 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permission_codes' => ['nullable', 'array'],
            'permission_codes.*' => ['string', 'exists:permissions,code'],
        ]);

        // Update function
        $fnction->update($request->only('code', 'name', 'description'));

        // Sync permissions if provided
        if (!empty($data['permission_codes'])) {
            $syncData = [];
            foreach ($data['permission_codes'] as $permCode) {
                $syncData[$permCode] = [
                    'fnc_perm_code' => $fnction->code . '.' . $permCode
                ];
            }
            $fnction->permissions()->sync($syncData);
        }


        return response()->json($fnction->load('permissions'));
    }


    // Delete function by code
    public function destroy($code)
    {
        $fnction = Fnction::where('code', $code)->firstOrFail();
        $fnction->delete(); // cascades to pivot table
        return response()->json(['message' => 'Function deleted successfully.']);
    }
}
