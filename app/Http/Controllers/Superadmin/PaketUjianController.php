<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncPaketUjianRequest;
use App\Models\Paket;
use App\Services\Ujian\PaketUjianSyncService;
use Illuminate\Http\RedirectResponse;

class PaketUjianController extends Controller
{
    public function __construct(
        private readonly PaketUjianSyncService $syncService
    ) {}

    public function sync(SyncPaketUjianRequest $request, Paket $paket): RedirectResponse
    {
        $this->syncService->sync($paket, $request->input('ujian_id', []));

        return back()->with('success', 'Ujian berhasil ditautkan ke paket.');
    }
}
