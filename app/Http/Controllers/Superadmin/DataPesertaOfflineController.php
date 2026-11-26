<?php

namespace App\Http\Controllers\Superadmin;

use App\Exports\Templates\KelasPesertaOfflineTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\KelasPesertaOfflineImport;
use App\Models\Kelas;
use App\Models\PesertaOffline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataPesertaOfflineController extends Controller
{
    /**
     * Display a listing of offline participants.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kelasFilter = $request->input('nama_kelas');
        $statusFilter = $request->input('status');

        $query = PesertaOffline::query()
            ->with(['ujians', 'kelas'])
            ->withCount('ujians')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor_peserta', 'like', "%{$search}%")
                        ->orWhere('nama_peserta', 'like', "%{$search}%")
                        ->orWhere('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('nomor_hp', 'like', "%{$search}%");
                });
            })
            ->when($kelasFilter, function ($q, $kelas) {
                $q->where(function ($sub) use ($kelas) {
                    $sub->where('nama_kelas', $kelas)
                        ->orWhere('kelas_id', $kelas);
                });
            })
            ->when($statusFilter === 'aktif', function ($q) {
                $q->where('is_blocked', false);
            })
            ->when($statusFilter === 'blocked', function ($q) {
                $q->where('is_blocked', true);
            })
            ->orderBy('created_at', 'desc');

        $peserta = $query->paginate(20)->withQueryString();

        // Ambil daftar kelas dari model Kelas dan nama_kelas unik
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('superadmin.data-peserta-offline.index', compact('peserta', 'kelasList', 'search', 'kelasFilter', 'statusFilter'));
    }

    /**
     * Show the form for creating a new offline participant.
     */
    public function create(Request $request): View
    {
        $kelasList = Kelas::where('status', 'aktif')->orderBy('nama_kelas')->get();

        $selectedKelasId = $request->input('kelas_id');
        $selectedNamaKelas = $request->input('nama_kelas');

        // Resolve ID jika yang dikirim hanya nama_kelas
        if (! $selectedKelasId && $selectedNamaKelas) {
            $selectedKelasId = Kelas::where('nama_kelas', $selectedNamaKelas)->value('id');
        }

        // Resolve nama jika yang dikirim hanya ID
        if ($selectedKelasId && ! $selectedNamaKelas) {
            $selectedNamaKelas = Kelas::where('id', $selectedKelasId)->value('nama_kelas');
        }

        return view('superadmin.data-peserta-offline.create', compact('kelasList', 'selectedKelasId', 'selectedNamaKelas'));
    }

    /**
     * Store a newly created offline participant.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_peserta' => ['required', 'string', 'max:50', 'unique:panritta_peserta_offline,nomor_peserta'],
            'nama_peserta' => ['required', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'exists:panritta_kelas,id'],
            'nama_kelas' => ['nullable', 'string', 'max:255'],
            'nomor_hp' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:4'],
        ], [
            'nomor_peserta.required' => 'Username / Nomor Peserta wajib diisi.',
            'nomor_peserta.unique' => 'Username / Nomor Peserta sudah terdaftar.',
            'nama_peserta.required' => 'Nama Peserta wajib diisi.',
        ]);

        $kelasId = $request->input('kelas_id');
        $namaKelas = null;

        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $namaKelas = $kelas?->nama_kelas;
        } elseif ($request->filled('nama_kelas')) {
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => trim($request->input('nama_kelas'))],
                ['status' => 'aktif']
            );
            $kelasId = $kelas->id;
            $namaKelas = $kelas->nama_kelas;
        }

        if (! $namaKelas) {
            return back()->withInput()->withErrors(['kelas_id' => 'Kelas / Gelombang wajib dipilih atau diisi.']);
        }

        $plainPassword = ! empty($validated['password'])
            ? $validated['password']
            : Str::upper(Str::random(6));

        $peserta = PesertaOffline::create([
            'nomor_peserta' => $validated['nomor_peserta'],
            'nama_peserta' => $validated['nama_peserta'],
            'kelas_id' => $kelasId,
            'nama_kelas' => $namaKelas,
            'nomor_hp' => $validated['nomor_hp'] ?? null,
            'kode_akses' => Hash::make($plainPassword),
            'kode_akses_plain' => $plainPassword,
            'is_active' => false,
            'is_blocked' => false,
        ]);

        $redirectParams = [];
        if ($namaKelas) {
            $redirectParams['nama_kelas'] = $namaKelas;
        }
        if ($kelasId) {
            $redirectParams['kelas_id'] = $kelasId;
        }

        return redirect()->route('superadmin.data-peserta-offline.index', $redirectParams)
            ->with('success', "Peserta {$peserta->nama_peserta} berhasil ditambahkan dengan Password / Kode Akses: {$plainPassword}");
    }

    /**
     * Show the form for editing the specified offline participant.
     */
    public function edit(PesertaOffline $data_peserta_offline): View
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('superadmin.data-peserta-offline.edit', [
            'peserta' => $data_peserta_offline,
            'kelasList' => $kelasList,
        ]);
    }

    /**
     * Update the specified offline participant.
     */
    public function update(Request $request, PesertaOffline $data_peserta_offline): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_peserta' => ['required', 'string', 'max:50', 'unique:panritta_peserta_offline,nomor_peserta,'.$data_peserta_offline->id],
            'nama_peserta' => ['required', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'exists:panritta_kelas,id'],
            'nama_kelas' => ['nullable', 'string', 'max:255'],
            'nomor_hp' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:4'],
            'is_blocked' => ['nullable', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:500'],
        ], [
            'nomor_peserta.required' => 'Username / Nomor Peserta wajib diisi.',
            'nomor_peserta.unique' => 'Username / Nomor Peserta sudah digunakan peserta lain.',
            'nama_peserta.required' => 'Nama Peserta wajib diisi.',
        ]);

        $kelasId = $request->input('kelas_id');
        $namaKelas = null;

        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $namaKelas = $kelas?->nama_kelas;
        } elseif ($request->filled('nama_kelas')) {
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => trim($request->input('nama_kelas'))],
                ['status' => 'aktif']
            );
            $kelasId = $kelas->id;
            $namaKelas = $kelas->nama_kelas;
        } else {
            $namaKelas = $data_peserta_offline->nama_kelas;
            $kelasId = $data_peserta_offline->kelas_id;
        }

        $updateData = [
            'nomor_peserta' => $validated['nomor_peserta'],
            'nama_peserta' => $validated['nama_peserta'],
            'kelas_id' => $kelasId,
            'nama_kelas' => $namaKelas,
            'nomor_hp' => $validated['nomor_hp'] ?? null,
            'is_blocked' => $request->boolean('is_blocked'),
            'blocked_reason' => $request->boolean('is_blocked') ? ($validated['blocked_reason'] ?? 'Diblokir oleh admin') : null,
            'blocked_at' => $request->boolean('is_blocked') ? now() : null,
        ];

        if (! empty($validated['password'])) {
            $updateData['kode_akses'] = Hash::make($validated['password']);
            $updateData['kode_akses_plain'] = $validated['password'];
        }

        $data_peserta_offline->update($updateData);

        return redirect()->route('superadmin.data-peserta-offline.index')
            ->with('success', "Data peserta {$data_peserta_offline->nama_peserta} berhasil diperbarui.");
    }

    /**
     * Remove the specified offline participant.
     */
    public function destroy(PesertaOffline $data_peserta_offline): RedirectResponse
    {
        $nama = $data_peserta_offline->nama_peserta;
        $data_peserta_offline->delete();

        return redirect()->route('superadmin.data-peserta-offline.index')
            ->with('success', "Peserta {$nama} berhasil dihapus dari data master.");
    }

    /**
     * Reset password / kode akses for participant.
     */
    public function resetPassword(PesertaOffline $data_peserta_offline): RedirectResponse
    {
        $plainPassword = Str::upper(Str::random(6));

        $data_peserta_offline->update([
            'kode_akses' => Hash::make($plainPassword),
            'kode_akses_plain' => $plainPassword,
            'kode_akses_reset_at' => now(),
            'kode_akses_reset_by' => auth()->id(),
        ]);

        return back()->with('success', "Password / Kode Akses peserta {$data_peserta_offline->nama_peserta} berhasil direset menjadi: {$plainPassword}");
    }

    /**
     * Bulk delete offline participants.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:panritta_peserta_offline,id'],
        ], [
            'ids.required' => 'Pilih minimal satu peserta untuk dihapus.',
        ]);

        $count = PesertaOffline::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "{$count} data peserta offline berhasil dihapus.");
    }

    /**
     * Download template Excel untuk impor peserta kelas.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new KelasPesertaOfflineTemplateExport, 'template_peserta_kelas.xlsx');
    }

    /**
     * Impor data peserta dari file Excel ke kelas yang ditentukan.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'kelas_id' => ['nullable', 'exists:panritta_kelas,id'],
            'nama_kelas' => ['nullable', 'string', 'max:255'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimpor.',
            'file.mimes' => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $kelasId = $request->input('kelas_id');
        $namaKelas = $request->input('nama_kelas');

        if ($kelasId && ! $namaKelas) {
            $namaKelas = Kelas::where('id', $kelasId)->value('nama_kelas');
        } elseif (! $kelasId && $namaKelas) {
            $kelas = Kelas::firstOrCreate(
                ['nama_kelas' => trim($namaKelas)],
                ['status' => 'aktif']
            );
            $kelasId = $kelas->id;
            $namaKelas = $kelas->nama_kelas;
        }

        if (! $namaKelas) {
            return back()->with('error', 'Kelas tujuan impor belum dipilih.');
        }

        try {
            $import = new KelasPesertaOfflineImport($kelasId, $namaKelas);
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengimpor data peserta: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getSuccessCount()} peserta ke kelas {$namaKelas}.";
        if ($import->getSkipCount() > 0) {
            $message .= " {$import->getSkipCount()} data dilewati.";
        }

        $redirect = redirect()->route('superadmin.data-peserta-offline.index', [
            'nama_kelas' => $namaKelas,
            'kelas_id' => $kelasId,
        ])->with('success', $message);

        if ($import->getErrors() !== []) {
            $redirect->with('import_errors', $import->getErrors());
        }

        return $redirect;
    }
}
