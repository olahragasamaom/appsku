<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubIndikatorRequest;
use App\Models\SubIndikator;
use Illuminate\Http\RedirectResponse;

class SubIndikatorController extends Controller
{
    public function store(SubIndikatorRequest $request): RedirectResponse
    {
        SubIndikator::create($request->validatedWithJenisUjian());

        return redirect()->back(fallback: route('pembuat-soal.dashboard'))
            ->with('success', 'Sub indikator berhasil dibuat.');
    }
}
