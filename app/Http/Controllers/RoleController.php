<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $roles = Role::orderByDesc('id')->paginate($perPage);

        return view('roles.index', compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * $perPage);
    }

    public function create()
    {
        $permission = Permission::orderBy('name')->get();
        return view('roles.create', compact('permission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|unique:roles,name',
            'permission' => 'required|array|min:1',
        ]);

        $role = Role::create([
            'name'       => $request->input('name'),
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $permissions = Permission::whereIn('id', $request->input('permission'))->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);

        $rolePermissions = Permission::select('permissions.*')
            ->join('role_has_permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_has_permissions.role_id', $role->id)
            ->orderBy('permissions.name')
            ->get();

        return view('roles.show', compact('role', 'rolePermissions'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $permission = Permission::where('guard_name', $role->guard_name)
            ->orderBy('name')
            ->get();

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permission', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'       => 'required|unique:roles,name,' . $role->id,
            'permission' => 'required|array|min:1',
        ]);

        $role->update(['name' => $request->input('name')]);

        // Accept either names or IDs coming from the form
        $permsInput = $request->input('permission');
        $isNumeric  = is_array($permsInput) && count($permsInput) && is_numeric($permsInput[0]);

        if ($isNumeric) {
            $perms = Permission::whereIn('id', $permsInput)
                ->where('guard_name', $role->guard_name)
                ->get();
            $role->syncPermissions($perms);
        } else {
            $role->syncPermissions($permsInput);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully');
    }
}
