<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Superadmin\PesertaController as SuperadminPesertaController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesertaController extends SuperadminPesertaController
{
    public function index(Request $request): View
    {
        $response = parent::index($request);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.peserta.index', $data);
        }
        
        return $response;
    }

    public function create(): View
    {
        $response = parent::create();
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.peserta.create', $data);
        }
        
        return $response;
    }

    public function edit(User $peserta): View
    {
        $response = parent::edit($peserta);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.peserta.edit', $data);
        }
        
        return $response;
    }
}
