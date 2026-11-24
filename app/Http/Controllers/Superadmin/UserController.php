<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::where('is_peserta', false)
            ->with(['company', 'userLevel', 'roles'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('superadmin.users.index', compact('users'));
    }

    public function create(): View
    {
        $userLevels = UserLevel::where('is_active', true)->get();

        return view('superadmin.users.create', compact('userLevels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_level_id' => ['nullable', 'exists:panritta_user_levels,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'user_level_id' => $validated['user_level_id'],
            'is_active' => $validated['is_active'] ?? true,
            'is_superadmin' => false,
            'is_peserta' => false,
        ]);

        return redirect()->route('superadmin.users.show', $user)
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user): View
    {
        $user->load(['company', 'userLevel', 'roles']);

        return view('superadmin.users.show', compact('user'));
    }
}
