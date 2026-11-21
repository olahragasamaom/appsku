<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\UjianController as SuperadminUjianController;
use App\Http\Requests\UjianRequest;
use App\Models\Ujian;
use Illuminate\Http\Request;
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

    public function edit(Ujian $ujian): View
    {
        $response = parent::edit($ujian);
        
        if ($response instanceof View) {
            $data = $response->getData();
            return view('pembuat-soal.ujian.edit', $data);
        }
        
        return $response;
    }
}
