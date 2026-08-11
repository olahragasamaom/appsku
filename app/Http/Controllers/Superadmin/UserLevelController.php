<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLevelRequest;
use App\Models\Module;
use App\Models\UserLevel;
use Database\Seeders\ModuleSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserLevelController extends Controller
{
    public function index(): View
    {
        $userLevels = UserLevel::with('role.permissions')
            ->orderBy('nama')
            ->paginate(15);

        $modules = Module::where('is_active', true)
            ->orderBy('urutan')
            ->get()
            ->groupBy('grup');

        $actions = ModuleSeeder::ACTIONS;

        return view('superadmin.user-levels.index', compact('userLevels', 'modules', 'actions'));
    }

    public function store(UserLevelRequest $request): RedirectResponse
    {
        $userLevel = UserLevel::create($request->levelAttributes());
        $userLevel->syncModulePermissions($request->moduleActions());

        return redirect()->route('superadmin.user-levels.index')
            ->with('success', 'Level user berhasil dibuat.');
    }

    public function update(UserLevelRequest $request, UserLevel $userLevel): RedirectResponse
    {
        $userLevel->update($request->levelAttributes());
        $userLevel->syncModulePermissions($request->moduleActions());

        return redirect()->route('superadmin.user-levels.index')
            ->with('success', 'Level user berhasil diperbarui.');
    }

    public function destroy(UserLevel $userLevel): RedirectResponse
    {
        $userLevel->delete();

        return redirect()->route('superadmin.user-levels.index')
            ->with('success', 'Level user berhasil dihapus.');
    }
}
