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
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $users = User::query()
            ->where('is_peserta', false)
            ->where('is_superadmin', false)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('userLevel', function ($ql) use ($search) {
                            $ql->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->with(['company', 'userLevel', 'roles'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

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
            'is_pengajar' => ['nullable', 'boolean'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'user_level_id' => $validated['user_level_id'] ?: null,
            'is_pengajar' => $request->boolean('is_pengajar'),
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

    public function editPassword(User $user): View
    {
        return view('superadmin.users.edit-password', compact('user'));
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('superadmin.users.show', $user)
            ->with('success', 'Password user berhasil diubah.');
    }
}
