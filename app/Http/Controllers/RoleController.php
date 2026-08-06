<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::all()->groupBy(fn($p) => explode(' ', $p->name)[1] ?? 'general');
        return view('waste_management.roles', compact('roles','permissions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name'=>'required|string|unique:roles,name']);
        Role::create(['name'=>$request->name,'guard_name'=>'web']);
        return redirect()->route('roles.index')->with('success', __('Role created'));
    }

    public function update(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('roles.index')->with('success', __('Role permissions updated'));
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin','user'])) return back()->with('error', __('Cannot delete system roles'));
        $role->delete();
        return redirect()->route('roles.index')->with('success', __('Role deleted'));
    }
}