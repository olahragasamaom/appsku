<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\PesertaController as SuperadminPesertaController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesertaController extends SuperadminPesertaController
{
    public function index(Request $request): View
    {
        $response = parent::index($request);
        
        if ($response instanceof View) {
            $data = $response->getData();
            return view('pembuat-soal.peserta.index', $data);
        }
        
        return $response;
    }
}
