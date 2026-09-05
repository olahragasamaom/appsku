<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginPesertaOfflineRequest;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\Ujian\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CONTROLLER: OfflinePortalController (P5-T6 revised)
 * ====================================
 * New flow: login → daftar ujian → ikuti ujian (dengan attendance check)
 */
class OfflinePortalController extends Controller
{
    public function __construct(
        private readonly AttemptService $attemptService
    ) {}

    public function index(): View
    {
        return view('peserta.ujian.offline.portal');
    }

    public function login(LoginPesertaOfflineRequest $request): RedirectResponse
    {
        $peserta = PesertaOffline::where('nomor_peserta', $request->nomor_peserta)
            ->first();

        if (! $peserta || ! Hash::check($request->kode_akses, $peserta->kode_akses)) {
            throw ValidationException::withMessages([
                'kode_akses' => 'Nomor peserta atau kode akses salah.',
            ]);
        }

        $request->session()->put('offline_peserta_id', $peserta->id);

        return redirect()->route('peserta.offline.exams');
    }

    public function exams(Request $request): View
    {
        $pesertaId = $request->session()->get('offline_peserta_id');

        if (! $pesertaId) {
            abort(403);
        }

        $peserta = PesertaOffline::findOrFail($pesertaId);

        // Get all exams where peserta has a kehadiran record
        $ujians = Ujian::whereHas('pesertaOfflineKehadiran', function ($query) use ($peserta) {
            $query->where('peserta_offline_id', $peserta->id);
        })
            ->with(['pesertaOfflineKehadiran' => function ($query) use ($peserta) {
                $query->where('peserta_offline_id', $peserta->id);
            }])
            ->where('tipe_ujian', 'offline_kelas')
            ->get();

        return view('peserta.ujian.offline.exams', compact('ujians', 'peserta'));
    }

    public function start(Request $request, Ujian $ujian): RedirectResponse
    {
        $pesertaId = $request->session()->get('offline_peserta_id');

        if (! $pesertaId) {
            abort(403);
        }

        $peserta = PesertaOffline::findOrFail($pesertaId);

        if ($ujian->status !== 'aktif') {
            abort(403, 'Ujian belum diaktifkan.');
        }

        $kehadiran = $peserta->kehadiran()
            ->where('ujian_id', $ujian->id)
            ->first();

        if (! $kehadiran || $kehadiran->status_kehadiran !== 'hadir') {
            abort(403, 'Anda belum dimarkir hadir.');
        }

        $attempt = UjianPeserta::create([
            'ujian_id' => $ujian->id,
            'status' => 'sedang_ujian',
            'waktu_mulai' => now(),
            'batas_waktu' => now()->addMinutes($ujian->durasi_ujian ?? 90),
        ]);

        $peserta->update(['ujian_peserta_id' => $attempt->id]);

        $request->session()->put([
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
        ]);

        return redirect()->route('peserta.ujian.kerjakan', $ujian->id);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['offline_peserta_id', 'offline_ujian_id', 'offline_attempt_id']);

        return redirect()->route('peserta.ujian.offline.portal')
            ->with('success', 'Anda telah logout.');
    }
}
