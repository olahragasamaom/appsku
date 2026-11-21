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
    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta (misal: /superadmin/ujian/5/peserta)
     * 
     * Keterangan:
     * Menampilkan DAFTAR PESERTA ONLINE yang terdaftar di ujian.
     * 
     * Filter: search by nama / username peserta
     * Paginasi 20 peserta per halaman
     * 
     * Tampilkan per peserta: nama, username, status (terdaftar/diblokir), aksi (blokir/unlock/hapus)
     * 
     * Form: Tidak ada, parameter GET saja (search)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($peserta->map(fn($p) => ['nama' => $p->user->name, 'status' => $p->status])->toArray());
     */
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

    /**
     * METHOD: available()
     * ===================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta/available
     * 
     * Keterangan:
     * Menampilkan DAFTAR PESERTA YANG BELUM TERDAFTAR di ujian ini.
     * Gunakan untuk menambah peserta baru ke ujian.
     * 
     * Filter: Hanya role "peserta", belum ada di ujian, search by nama/username
     * 
     * Form: Tidak ada, cuman view selection untuk attach multiple peserta
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($available->map(fn($u) => ['id' => $u->id, 'nama' => $u->name])->toArray());
     */
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

    /**
     * METHOD: store()
     * ===============
     * URL Contoh: POST /superadmin/ujian/{ujian}/peserta
     * 
     * Keterangan:
     * Menambahkan MULTIPLE PESERTA ke ujian (bulk attach).
     * 
     * Validasi:
     * - user_id: array of integer, min 1
     * - Each ID must exist in users table
     * 
     * Logic:
     * 1. Cek user_id yang sudah terdaftar di ujian, skip
     * 2. Validasi user yang dipilih punya role "peserta"
     * 3. Buat UjianPeserta record (status = terdaftar)
     * 4. Count berapa yang berhasil di-add, tampilkan message
     * 
     * Form: Checkbox list di halaman available() (POST method)
     *       Input: user_id[] (multiselect)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Add peserta to ujian '.$ujian->id, ['count' => $added, 'user_ids' => $validated['user_id']]);
     */
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

    /**
     * METHOD: toggleBlokir()
     * ======================
     * URL Contoh: PATCH /superadmin/ujian/{ujian}/peserta/{peserta}/toggle-blokir
     * 
     * Keterangan:
     * TOGGLE status peserta: terdaftar <-> diblokir.
     * 
     * Jika status = diblokir: ubah ke terdaftar (unlock)
     * Jika status = terdaftar: ubah ke diblokir (block)
     * 
     * Peserta yang diblokir TIDAK bisa login ke ujian.
     * 
     * Form: Button toggle block/unlock di daftar peserta (PATCH dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Toggle blokir peserta '.$peserta.' ujian '.$ujian->id);
     */
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

    /**
     * METHOD: destroy()
     * =================
     * URL Contoh: DELETE /superadmin/ujian/{ujian}/peserta/{peserta}
     * 
     * Keterangan:
     * Menghapus ATTACHMENT peserta dari ujian (remove enrollment).
     * Peserta user tetap ada, hanya dilepas dari ujian ini.
     * 
     * Jika peserta sudah punya hasil ujian: result tetap tersimpan di DB.
     * 
     * Form: Tombol remove/eject di daftar peserta (DELETE method)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Remove peserta '.$peserta.' from ujian '.$ujian->id);
     */
    public function destroy(Ujian $ujian, int $peserta): RedirectResponse
    {
        $ujianPeserta = $ujian->peserta()->findOrFail($peserta);
        $ujianPeserta->delete();

        return redirect()->route('superadmin.ujian.peserta.index', $ujian)
            ->with('success', 'Peserta berhasil dikeluarkan dari ujian.');
    }

    /**
     * METHOD: exportPdf()
     * ===================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta/export/pdf
     * 
     * Keterangan:
     * Download DAFTAR PESERTA + AKUN dalam format PDF.
     * Cocok untuk cetak & distribusi ke peserta (login credentials).
     * 
     * Gunakan Barryvdh\DomPDF untuk generate PDF dari Blade template.
     * 
     * Form: Tidak ada, button download di index peserta
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Export peserta PDF untuk ujian '.$ujian->id);
     */
    public function exportPdf(Ujian $ujian): Response
    {
        $peserta = $ujian->peserta()->with('user')->get();

        $pdf = Pdf::loadView('superadmin.ujian.peserta.akun-pdf', compact('ujian', 'peserta'));

        return $pdf->download('daftar-akun-'.str($ujian->nama_ujian)->slug().'.pdf');
    }

    /**
     * METHOD: exportExcel()
     * =====================
     * URL Contoh: GET /superadmin/ujian/{ujian}/peserta/export/excel
     * 
     * Keterangan:
     * Download DAFTAR PESERTA + AKUN dalam format Excel (.xlsx).
     * Cocok untuk import ke sistem lain, atau editing lebih lanjut.
     * 
     * Gunakan Maatwebsite\Excel untuk export, class UjianPesertaAkunExport handle format.
     * 
     * Form: Tidak ada, button download di index peserta
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Export peserta Excel untuk ujian '.$ujian->id);
     */
    public function exportExcel(Ujian $ujian): BinaryFileResponse
    {
        return Excel::download(
            new UjianPesertaAkunExport($ujian),
            'daftar-akun-'.str($ujian->nama_ujian)->slug().'.xlsx'
        );
    }
}
