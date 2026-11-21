<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Superadmin\PaketController as SuperadminPaketController;
use App\Models\Paket;
use Illuminate\View\View;

class PaketController extends SuperadminPaketController
{
    public function index(): View
    {
        $response = parent::index();
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.paket.index', $data);
        }
        
        return $response;
    }

    public function create(): View
    {
        $response = parent::create();
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.paket.create', $data);
        }
        
        return $response;
    }

    public function edit(Paket $paket): View
    {
        $response = parent::edit($paket);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.paket.edit', $data);
        }
        
        return $response;
    }

    public function store(\App\Http\Requests\PaketRequest $request): \Illuminate\Http\RedirectResponse
    {
        parent::store($request);
        return redirect()->route('keuangan.paket.index')->with('success', 'Paket berhasil dibuat.');
    }

    public function update(\App\Http\Requests\PaketRequest $request, Paket $paket): \Illuminate\Http\RedirectResponse
    {
        parent::update($request, $paket);
        return redirect()->route('keuangan.paket.index')->with('success', 'Paket berhasil diupdate.');
    }

    public function destroy(Paket $paket): \Illuminate\Http\RedirectResponse
    {
        parent::destroy($paket);
        return redirect()->route('keuangan.paket.index')->with('success', 'Paket berhasil dihapus.');
    }
}
