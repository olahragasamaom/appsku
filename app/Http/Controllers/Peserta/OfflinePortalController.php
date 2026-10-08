<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginPesertaOfflineRequest;
use App\Models\OfflineParticipantSession;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\Ujian\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * CONTROLLER: OfflinePortalController (P5-T6 revised + monitoring)
 * ====================================
 * New flow: login → daftar ujian → ikuti ujian (dengan attendance check)
 * With multi-device prevention & pengawas monitoring support.
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

        // Cek apakah peserta di-blokir
        if ($peserta->is_blocked) {
            throw ValidationException::withMessages([
                'kode_akses' => 'Akun Anda telah diblokir. Alasan: '.($peserta->blocked_reason ?? 'Tidak dijelaskan'),
            ]);
        }

        // Force logout any existing active sessions (anti multi-device)
        OfflineParticipantSession::where('peserta_offline_id', $peserta->id)
            ->whereIn('status', ['logged_in', 'sedang_ujian'])
            ->update([
                'status' => 'logout',
                'logout_at' => now(),
                'logout_reason' => 'Login dari perangkat lain',
            ]);

        // Create new session
        $sessionToken = Str::random(64);
        OfflineParticipantSession::create([
            'peserta_offline_id' => $peserta->id,
            'ujian_id' => null,
            'session_token' => $sessionToken,
            'status' => 'logged_in',
            'device_info' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'login_at' => now(),
            'last_activity_at' => now(),
        ]);

        $request->session()->put('offline_peserta_id', $peserta->id);
        $request->session()->put('offline_session_token', $sessionToken);

        return redirect()->route('peserta.offline.exams');
    }

    public function exams(Request $request): View
    {
        $pesertaId = $request->session()->get('offline_peserta_id');

        if (! $pesertaId) {
            abort(403);
        }

        $peserta = PesertaOffline::findOrFail($pesertaId);

        // Get all ujian offline where peserta is registered (ujian_id matches)
        // and ujian is aktif with date range check
        $ujians = Ujian::where('id', $peserta->ujian_id)
            ->where('tipe_ujian', 'offline_kelas')
            ->where('status', 'aktif')
            ->where(function ($query) {
                $query->whereDate('tanggal_ujian', '>=', now()->startOfDay())
                    ->whereDate('tanggal_ujian', '<=', now()->addMonths(6)->endOfDay());
            })
            ->with(['pesertaOfflineKehadiran' => function ($query) use ($peserta) {
                $query->where('peserta_offline_id', $peserta->id);
            }])
            ->orderBy('tanggal_ujian', 'asc')
            ->get();

        return view('peserta.ujian.offline.exams', compact('ujians', 'peserta'));
    }

    public function start(Request $request, Ujian $ujian): RedirectResponse
    {
        $pesertaId = $request->session()->get('offline_peserta_id');
        $sessionToken = $request->session()->get('offline_session_token');

        if (! $pesertaId || ! $sessionToken) {
            abort(403);
        }

        $peserta = PesertaOffline::findOrFail($pesertaId);

        // Validasi 1: Ujian harus aktif
        if ($ujian->status !== 'aktif') {
            abort(403, 'Ujian belum diaktifkan.');
        }

        // Validasi 2: Ujian harus dalam rentang tanggal
        if ($ujian->tanggal_ujian && $ujian->tanggal_ujian->isPast()) {
            abort(403, 'Ujian ini sudah berakhir.');
        }

        // Validasi 3: Peserta harus terdaftar di ujian ini
        if ($peserta->ujian_id !== $ujian->id) {
            abort(403, 'Anda tidak terdaftar di ujian ini.');
        }

        // Validasi 4: Peserta harus diaktifkan (is_active = true)
        if (! $peserta->is_active) {
            abort(403, 'Akun Anda belum diaktifkan. Hubungi admin untuk aktivasi.');
        }

        // Validasi 5: Peserta harus ditandai hadir
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

        // Update session status: peserta sekarang sedang ujian
        OfflineParticipantSession::where('session_token', $sessionToken)
            ->update([
                'ujian_id' => $ujian->id,
                'status' => 'sedang_ujian',
                'last_activity_at' => now(),
            ]);

        $request->session()->put([
            'offline_ujian_id' => $ujian->id,
            'offline_attempt_id' => $attempt->id,
        ]);

        return redirect()->route('peserta.ujian.kerjakan', $ujian->id);
    }

    public function logout(Request $request): RedirectResponse
    {
        $sessionToken = $request->session()->get('offline_session_token');

        // Update session status di database
        if ($sessionToken) {
            OfflineParticipantSession::where('session_token', $sessionToken)
                ->update([
                    'status' => 'logout',
                    'logout_at' => now(),
                    'logout_reason' => 'Logout oleh peserta',
                ]);
        }

        $request->session()->forget([
            'offline_peserta_id',
            'offline_session_token',
            'offline_ujian_id',
            'offline_attempt_id',
        ]);

        return redirect()->route('peserta.ujian.offline.portal')
            ->with('success', 'Anda telah logout.');
    }
}
