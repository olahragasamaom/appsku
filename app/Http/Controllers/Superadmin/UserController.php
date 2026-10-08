<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function show(User $user): View
    {
        $user->load(['company', 'userLevel', 'roles']);

        return view('superadmin.users.show', compact('user'));
    }
}
