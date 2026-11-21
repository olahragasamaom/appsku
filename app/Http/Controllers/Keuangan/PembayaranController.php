<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Superadmin\PembayaranController as SuperadminPembayaranController;
use App\Models\PesertaPembayaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends SuperadminPembayaranController
{
    public function index(Request $request): View
    {
        $response = parent::index($request);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.pembayaran.index', $data);
        }
        
        return $response;
    }

    public function show(PesertaPembayaran $pembayaran): View
    {
        $response = parent::show($pembayaran);
        
        if ($response instanceof View) {
            $data = $response->getData();
            $data['routePrefix'] = 'keuangan';
            return view('keuangan.pembayaran.show', $data);
        }
        
        return $response;
    }

    public function verify(Request $request, PesertaPembayaran $pembayaran): \Illuminate\Http\RedirectResponse
    {
        parent::verify($request, $pembayaran);
        return redirect()->route('keuangan.pembayaran.index')->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, PesertaPembayaran $pembayaran): \Illuminate\Http\RedirectResponse
    {
        parent::reject($request, $pembayaran);
        return redirect()->route('keuangan.pembayaran.index')->with('success', 'Pembayaran ditolak.');
    }
}
