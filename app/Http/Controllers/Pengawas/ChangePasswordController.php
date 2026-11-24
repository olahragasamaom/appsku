<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function index(): View
    {
        return view('pengawas.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        auth()->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
