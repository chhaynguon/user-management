<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    // List all groups
    public function index()
    {
        $groups = Group::with('roles')->get();
        return response()->json($groups);
        
    }

    // Show single group by code
    public function show($code)
    {
        $group = Group::where('code', $code)->with('roles','users')->firstOrFail();
        return response()->json($group);
    }

    // Create new group
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:100','unique:groups,code'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            // optional initial roles: array of role codes
            'role_codes' => ['nullable','array'],
            'role_codes.*' => ['string','exists:roles,code'],
        ]);

        $group = Group::create($request->only('code','name','description'));

        // attach roles if provided
        if (!empty($data['role_codes'])) {
            $group->roles()->sync($data['role_codes']);
        }

        return response()->json($group->load('roles'), 201);
    }

    // Update group by code
    public function update(Request $request, $code)
    {
        $group = Group::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'code' => ['required','string','max:100', Rule::unique('groups','code')->ignore($group->code, 'code')],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'role_codes' => ['nullable','array'],
            'role_codes.*' => ['string','exists:roles,code'],
        ]);

        $group->update($request->only('code','name','description'));

        if (array_key_exists('role_codes', $data)) {
            $group->roles()->sync($data['role_codes'] ?? []);
        }

        return response()->json($group->load('roles'));
    }

    // Delete group by code
    public function destroy($code)
    {
        $group = Group::where('code', $code)->firstOrFail();
        $group->delete();

        return response()->json(['message' => 'Group deleted successfully.']);
    }
}
