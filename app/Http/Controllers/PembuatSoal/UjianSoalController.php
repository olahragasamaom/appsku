<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\UjianSoalController as SuperadminUjianSoalController;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UjianSoalController extends SuperadminUjianSoalController
{
    public function index(Request $request, Ujian $ujian): View
    {
        $response = parent::index($request, $ujian);
        
        if ($response instanceof View) {
            $data = $response->getData();
            return view('pembuat-soal.ujian.soal.index', $data);
        }
        
        return $response;
    }

    public function create(Request $request, Ujian $ujian): View
    {
        $soalOptions = \App\Models\Soal::orderBy('created_at', 'desc')->get();
        
        return view('pembuat-soal.ujian.soal.create', [
            'ujian' => $ujian,
            'soalOptions' => $soalOptions,
        ]);
    }

    public function store(Request $request, Ujian $ujian): RedirectResponse
    {
        return parent::store($request, $ujian);
    }

    public function detach(Ujian $ujian, $ujianSoal): RedirectResponse
    {
        return parent::destroy($ujian, $ujianSoal);
    }

    public function attach(\Illuminate\Http\Request $request, Ujian $ujian): RedirectResponse
    {
        return parent::attach($request, $ujian);
    }

    public function import(\Illuminate\Http\Request $request, Ujian $ujian): RedirectResponse
    {
        return parent::import($request, $ujian);
    }
}
