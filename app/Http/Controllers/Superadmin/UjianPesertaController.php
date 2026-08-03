<?php

namespace App\Http\Controllers\Superadmin;

use App\Exports\UjianPesertaAkunExport;
use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UjianPesertaController extends Controller
{
    public function index(Request $request, Ujian $ujian): View
    {
        $peserta = $ujian->peserta()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.ujian.peserta.index', compact('ujian', 'peserta'));
    }

    public function available(Request $request, Ujian $ujian): View
    {
        $allocatedUserIds = $ujian->peserta()->pluck('user_id')->all();

        $available = User::query()
            ->peserta()
            ->whereNotIn('id', $allocatedUserIds)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('superadmin.ujian.peserta.available', compact('ujian', 'available'));
    }

    public function store(Request $request, Ujian $ujian): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'array', 'min:1'],
            'user_id.*' => ['integer', 'exists:users,id'],
        ]);

        $existing = $ujian->peserta()->pluck('user_id')->all();
        $added = 0;

        foreach ($validated['user_id'] as $userId) {
            if (in_array((int) $userId, $existing, true)) {
                continue;
            }

            if (! User::whereKey($userId)->peserta()->exists()) {
                continue;
            }

            $ujian->peserta()->create([
                'user_id' => $userId,
                'status' => 'terdaftar',
            ]);

            $added++;
        }

        return redirect()->route('superadmin.ujian.peserta.index', $ujian)
            ->with('success', "{$added} peserta berhasil ditambahkan ke ujian.");
    }

    public function toggleBlokir(Ujian $ujian, int $peserta): RedirectResponse
    {
        $ujianPeserta = $ujian->peserta()->findOrFail($peserta);

        $ujianPeserta->status = $ujianPeserta->status === 'diblokir' ? 'terdaftar' : 'diblokir';
        $ujianPeserta->save();

        $message = $ujianPeserta->status === 'diblokir'
            ? 'Peserta berhasil diblokir.'
            : 'Peserta berhasil diaktifkan kembali.';

        return redirect()->route('superadmin.ujian.peserta.index', $ujian)->with('success', $message);
    }

    public function destroy(Ujian $ujian, int $peserta): RedirectResponse
    {
        $ujianPeserta = $ujian->peserta()->findOrFail($peserta);
        $ujianPeserta->delete();

        return redirect()->route('superadmin.ujian.peserta.index', $ujian)
            ->with('success', 'Peserta berhasil dikeluarkan dari ujian.');
    }

    public function exportPdf(Ujian $ujian): Response
    {
        $peserta = $ujian->peserta()->with('user')->get();

        $pdf = Pdf::loadView('superadmin.ujian.peserta.akun-pdf', compact('ujian', 'peserta'));

        return $pdf->download('daftar-akun-'.str($ujian->nama_ujian)->slug().'.pdf');
    }

    public function exportExcel(Ujian $ujian): BinaryFileResponse
    {
        return Excel::download(
            new UjianPesertaAkunExport($ujian),
            'daftar-akun-'.str($ujian->nama_ujian)->slug().'.xlsx'
        );
    }
}
