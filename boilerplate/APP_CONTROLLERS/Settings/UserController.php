<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::orderBy('name')->get();

        return view('settings.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();

        return view('settings.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('settings.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update role
        $user->syncRoles([$validated['role']]);

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Don't allow deleting the only admin
        if ($user->hasRole('admin') && User::role('admin')->count() === 1) {
            return redirect()->route('settings.users.index')
                ->with('error', 'Tidak bisa menghapus satu-satunya admin.');
        }

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
