<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\SoalController as SuperadminSoalController;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoalController extends SuperadminSoalController
{
    public function index(Request $request): View
    {
        $response = parent::index($request);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'pembuat-soal';
            return view('pembuat-soal.soal.index', $data);
        }
        
        return $response;
    }

    public function create(Request $request): View
    {
        $response = parent::create($request);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'pembuat-soal';
            return view('pembuat-soal.soal.create', $data);
        }
        
        return $response;
    }

    public function edit(Soal $soal): View
    {
        $response = parent::edit($soal);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'pembuat-soal';
            return view('pembuat-soal.soal.edit', $data);
        }
        
        return $response;
    }

    public function preview(Soal $soal): \Illuminate\View\View
    {
        return parent::preview($soal);
    }

    public function subJenisUjianOptions(\App\Models\JenisUjian $jenisUjian): \Illuminate\Http\JsonResponse
    {
        return parent::subJenisUjianOptions($jenisUjian);
    }

    public function subIndikatorOptions(\App\Models\SubJenisUjian $subJenisUjian): \Illuminate\Http\JsonResponse
    {
        return parent::subIndikatorOptions($subJenisUjian);
    }

    public function uploadGambarEditor(Request $request): \Illuminate\Http\JsonResponse
    {
        return parent::uploadGambarEditor($request);
    }

    public function update(\App\Http\Requests\SoalRequest $request, Soal $soal): \Illuminate\Http\RedirectResponse
    {
        $response = parent::update($request, $soal);
        
        // Redirect ke pembuat-soal routes
        if ($request->filled('ujian_id')) {
            $ujian = \App\Models\Ujian::find($request->ujian_id);
            $jenisUjianId = $request->resolveSubJenisUjian()?->jenis_ujian_id;

            if ($ujian && $jenisUjianId) {
                return redirect()
                    ->route('pembuat-soal.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
                    ->with('success', 'Soal berhasil diupdate.');
            }
        }

        return redirect()->route('pembuat-soal.soal.index')
            ->with('success', 'Soal berhasil diupdate.');
    }

    public function store(\App\Http\Requests\SoalRequest $request): \Illuminate\Http\RedirectResponse
    {
        parent::store($request);
        
        if ($request->filled('ujian_id')) {
            $ujian = \App\Models\Ujian::find($request->ujian_id);
            $jenisUjianId = $request->resolveSubJenisUjian()?->jenis_ujian_id;

            if ($ujian && $jenisUjianId) {
                return redirect()
                    ->route('pembuat-soal.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => $jenisUjianId])
                    ->with('success', 'Soal berhasil dibuat.');
            }
        }

        return redirect()->route('pembuat-soal.soal.index')
            ->with('success', 'Soal berhasil dibuat.');
    }
}
