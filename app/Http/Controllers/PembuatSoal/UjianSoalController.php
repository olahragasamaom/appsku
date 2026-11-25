<?php

namespace App\Http\Controllers\PembuatSoal;

use App\Http\Controllers\Superadmin\UjianSoalController as SuperadminUjianSoalController;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianSoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UjianSoalController extends SuperadminUjianSoalController
{
    public function index(Request $request, Ujian $ujian): View
    {
        $this->authorizeUjian($ujian);

        return parent::index($request, $ujian);
    }

    public function create(Request $request, Ujian $ujian): View
    {
        $soalOptions = Soal::orderBy('created_at', 'desc')->get();

        return view('pembuat-soal.ujian.soal.create', [
            'ujian' => $ujian,
            'soalOptions' => $soalOptions,
        ]);
    }

    public function store(Request $request, Ujian $ujian): RedirectResponse
    {
        return $this->attach($request, $ujian);
    }

    public function detach(Ujian $ujian, UjianSoal $ujianSoal): RedirectResponse
    {
        return parent::detach($ujian, $ujianSoal);
    }

    public function attach(Request $request, Ujian $ujian): RedirectResponse
    {
        return parent::attach($request, $ujian);
    }

    public function import(Request $request, Ujian $ujian): RedirectResponse
    {
        return parent::importExcel($request, $ujian);
    }

    public function finalisasi(Request $request, Ujian $ujian): View
    {
        $this->authorizeUjian($ujian);

        $response = parent::finalisasi($request, $ujian);

        $data = $response->getData();

        return view('pembuat-soal.ujian.soal.finalisasi', $data);
    }

    public function reorder(Request $request, Ujian $ujian): JsonResponse
    {
        $this->authorizeUjian($ujian);

        return parent::reorder($request, $ujian);
    }

    public function finalize(Ujian $ujian): RedirectResponse
    {
        $this->authorizeUjian($ujian);

        parent::finalize($ujian);

        return redirect()
            ->route('pembuat-soal.ujian.soal.finalisasi', $ujian)
            ->with('success', 'Ujian berhasil difinalisasi.');
    }

    public function unfinalize(Ujian $ujian): RedirectResponse
    {
        $this->authorizeUjian($ujian);

        parent::unfinalize($ujian);

        return redirect()
            ->route('pembuat-soal.ujian.soal.finalisasi', $ujian)
            ->with('success', 'Finalisasi ujian dibatalkan.');
    }

    private function authorizeUjian(Ujian $ujian): void
    {
        $user = auth()->user();
        if ($user && ($user->isSuperAdmin() || $user->isPembuatSoal() || $user->isPengajar() || $ujian->dibuat_oleh === $user->id)) {
            return;
        }

        abort(403);
    }
}
