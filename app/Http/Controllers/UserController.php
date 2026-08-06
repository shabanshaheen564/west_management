<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name','like',"%$q%")->orWhere('email','like',"%$q%"));
        }
        if ($request->filled('role')) $query->role($request->role);

        $users = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $roles = Role::all();
        $stats = [
            'total'    => User::count(),
            'active'   => User::where('is_active',true)->count(),
            'inactive' => User::where('is_active',false)->count(),
        ];
        return view('waste_management.users', compact('users','roles','stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => bcrypt($validated['password']),
        ]);
        $user->assignRole($validated['role']);
        return redirect()->route('users.index')->with('success', __('User created successfully'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users,email,'.$user->id,
            'phone'     => 'nullable|string',
            'password'  => 'nullable|min:8|confirmed',
            'role'      => 'required|exists:roles,name',
            'is_active' => 'nullable|boolean',
        ]);
        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
        if (!empty($validated['password'])) $data['password'] = bcrypt($validated['password']);
        $user->update($data);
        $user->syncRoles([$validated['role']]);
        return redirect()->route('users.index')->with('success', __('User updated successfully'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', __('Cannot delete your own account'));
        $user->delete();
        return redirect()->route('users.index')->with('success', __('User deleted'));
    }
}