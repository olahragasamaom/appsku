<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\UjianController as SuperadminUjianController;
use App\Http\Requests\UjianRequest;
use App\Models\Ujian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UjianController extends SuperadminUjianController
{
    public function index(Request $request): View
    {
        $response = parent::index($request);
        
        if ($response instanceof View) {
            $ujians = $response->getData()['ujians'];
            return view('pembuat-soal.ujian.index', ['ujians' => $ujians]);
        }
        
        return $response;
    }

    public function create(): View
    {
        $response = parent::create();
        
        if ($response instanceof View) {
            $data = $response->getData();
            return view('pembuat-soal.ujian.create', $data);
        }
        
        return $response;
    }

    public function store(UjianRequest $request): RedirectResponse
    {
        $ujian = DB::transaction(function () use ($request) {
            $ujian = Ujian::create($this->buildData($request));
            $this->syncJenisUjian($ujian, $request);

            return $ujian;
        });

        return redirect()->route('pembuat-soal.ujian.index')
            ->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Ujian $ujian): View
    {
        $response = parent::edit($ujian);
        
        if ($response instanceof View) {
            $data = $response->getData();
            return view('pembuat-soal.ujian.edit', $data);
        }
        
        return $response;
    }

    public function update(UjianRequest $request, Ujian $ujian): RedirectResponse
    {
        DB::transaction(function () use ($request, $ujian) {
            $ujian->update($this->buildData($request, $ujian));
            $this->syncJenisUjian($ujian, $request);
        });

        return redirect()->route('pembuat-soal.ujian.index')
            ->with('success', 'Ujian berhasil diupdate.');
    }

    public function updateStatus(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,aktif,selesai'],
        ]);

        $ujian->update(['status' => $validated['status']]);

        return redirect()->route('pembuat-soal.ujian.index')
            ->with('success', 'Status ujian berhasil diubah.');
    }
}
