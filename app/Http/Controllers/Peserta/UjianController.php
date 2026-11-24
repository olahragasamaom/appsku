<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJawabanRequest;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\RandomSoalService;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UjianController extends Controller
{
    public function __construct(
        private readonly UjianScoringService $scoring,
        private readonly RandomSoalService $randomSoal
    ) {}

    public function show(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if ($peserta && $peserta->status === 'selesai') {
            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        \Log::info('show() - konfirmasi page', ['ujian_id' => $ujian->id, 'peserta_id' => $peserta?->id]);

        return view('peserta.ujian.konfirmasi', compact('ujian', 'peserta'));
    }

    public function start(Request $request, Ujian $ujian): RedirectResponse
    {
        $user = $request->user();

        abort_unless($ujian->status === 'aktif', 403, 'Ujian tidak aktif.');

        $peserta = $ujian->peserta()->where('user_id', $user->id)->first();

        if ($ujian->isOffline()) {
            $request->validate(['token' => ['required', 'string']]);

            if (! $peserta) {
                return back()->withErrors(['token' => 'Anda tidak terdaftar pada ujian ini.']);
            }

            if ($peserta->status === 'diblokir') {
                return back()->withErrors(['token' => 'Akun Anda diblokir oleh pengawas.']);
            }

            if (! hash_equals((string) $ujian->token_ujian, (string) $request->input('token'))) {
                return back()->withErrors(['token' => 'Token ujian salah.']);
            }
        }

        if (! $peserta) {
            abort_unless($ujian->isOnline(), 403);

            $peserta = $ujian->peserta()->create([
                'user_id' => $user->id,
                'status' => 'terdaftar',
            ]);
        }

        if ($peserta->status === 'selesai') {
            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        if ($peserta->status !== 'sedang_ujian') {
            $peserta->update([
                'status' => 'sedang_ujian',
                'waktu_mulai' => now(),
            ]);
        }

        \Log::info('start() - acak_soal check', [
            'ujian_id' => $ujian->id,
            'acak_soal' => $ujian->acak_soal,
            'soal_order_exists' => ! is_null($peserta->soal_order),
            'peserta_id' => $peserta->id,
        ]);

        if ($ujian->acak_soal && ! $peserta->soal_order) {
            $ujian->load('ujianJenisUjians.jenisUjian');
            $activeJenisId = (int) $request->input('jenis_ujian_id', $ujian->ujianJenisUjians->first()?->jenis_ujian_id ?? 0);
            \Log::info('start() - determining jenis_ujian_id', [
                'from_request' => $request->input('jenis_ujian_id'),
                'activeJenisId' => $activeJenisId,
                'ujian_jenis_count' => $ujian->ujianJenisUjians->count(),
            ]);
            if ($activeJenisId > 0) {
                $soalOrder = $this->randomSoal->generateSoalOrder($ujian, $activeJenisId);
                \Log::info('start() - soal_order generated', ['count' => count($soalOrder), 'order' => json_encode($soalOrder)]);
                $this->randomSoal->saveSoalOrder($peserta, $soalOrder);
                $peserta->refresh();
                \Log::info('start() - soal_order saved', ['soal_order' => json_encode($peserta->soal_order)]);
            } else {
                \Log::warning('start() - activeJenisId is 0, skipping random generation');
            }
        }

        return redirect()->route('peserta.ujian.kerjakan', $ujian);
    }

    public function kerjakan(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        \Log::info("kerjakan: peserta={$peserta?->id}, status={$peserta?->status}, ujian={$ujian->id}");

        if (! $peserta || $peserta->status === 'terdaftar') {
            return redirect()->route('peserta.ujian.show', $ujian);
        }

        if ($peserta->status === 'selesai') {
            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        if ($ujian->acak_soal && ! $peserta->soal_order) {
            $ujian->load('ujianJenisUjians.jenisUjian');
            $activeJenisId = (int) $ujian->ujianJenisUjians->first()?->jenis_ujian_id ?? 0;
            \Log::info('kerjakan() - random check', ['activeJenisId' => $activeJenisId, 'ujian_jenis_count' => $ujian->ujianJenisUjians->count()]);
            if ($activeJenisId > 0) {
                $soalOrder = $this->randomSoal->generateSoalOrder($ujian, $activeJenisId);
                \Log::info('kerjakan() - soalOrder generated', ['count' => count($soalOrder), 'order' => json_encode($soalOrder)]);
                if (! empty($soalOrder)) {
                    $this->randomSoal->saveSoalOrder($peserta, $soalOrder);
                    $peserta->refresh();
                    \Log::info('kerjakan() - soal_order saved', ['soal_order' => json_encode($peserta->soal_order)]);
                } else {
                    \Log::warning('kerjakan() - soalOrder is empty, skipping save');
                }
            }
        }

        if ($peserta->is_paused) {
            return redirect()->route('peserta.dashboard')->with('warning', 'Ujian Anda dihentikan sementara oleh pengawas. Silakan login kembali untuk melanjutkan.');
        }

        if ($this->deadlinePassed($ujian, $peserta)) {
            $this->autoSubmit($peserta);

            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $ujianSoals = $this->randomSoal->reorderSoalsByList($ujianSoals, $peserta->soal_order);

        $jawaban = $peserta->jawaban()->pluck('jawaban', 'ujian_soal_id');
        $sisaDetik = $this->sisaDetik($ujian, $peserta);

        return view('peserta.ujian.kerjakan', compact('ujian', 'peserta', 'ujianSoals', 'jawaban', 'sisaDetik'));
    }

    public function saveAnswer(StoreJawabanRequest $request, Ujian $ujian): JsonResponse
    {
        $peserta = $request->resolvePeserta();

        if (! $peserta) {
            abort(401);
        }

        // Guard: reject save jika deadline sudah lewat atau status sudah selesai
        if ($peserta->status === 'selesai') {
            return response()->json([
                'saved' => false,
                'expired' => true,
                'message' => 'Ujian sudah selesai.',
            ], 403);
        }

        if ($this->deadlinePassed($ujian, $peserta)) {
            $this->autoSubmit($peserta);

            return response()->json([
                'saved' => false,
                'expired' => true,
                'message' => 'Batas waktu ujian telah habis.',
                'redirect' => route('peserta.ujian.hasil', $ujian),
            ], 403);
        }

        $validated = $request->validated();

        $ujianSoal = $ujian->ujianSoals()->with('soal')->findOrFail($validated['ujian_soal_id']);

        $score = $this->scoring->scoreAnswer($ujianSoal->soal, $validated['jawaban'] ?? null, $ujian, $peserta);

        $peserta->jawaban()->updateOrCreate(
            ['ujian_soal_id' => $ujianSoal->id],
            [
                'soal_id' => $ujianSoal->soal_id,
                'jenis_ujian_id' => $ujianSoal->jenis_ujian_id,
                'jawaban' => $validated['jawaban'] ?? null,
                'nilai' => $score['nilai'],
                'benar' => $score['benar'],
            ]
        );

        // Update last_activity_at untuk tracking inactivity timeout
        $peserta->forceFill(['last_activity_at' => now()])->save();

        return response()->json(['saved' => true]);
    }

    /**
     * Heartbeat endpoint: client calls this periodically to verify deadline.
     * If deadline passed, backend will auto-finalize and return expired=true.
     * Juga update last_activity_at untuk tracking inactivity timeout.
     */
    public function heartbeat(Request $request, Ujian $ujian): JsonResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse || ! $peserta) {
            return response()->json([
                'expired' => true,
                'message' => 'Sesi tidak valid.',
                'redirect' => route('home'),
            ], 401);
        }

        // Jika sudah selesai, redirect ke hasil
        if ($peserta->status === 'selesai') {
            return response()->json([
                'expired' => true,
                'message' => 'Ujian sudah selesai.',
                'redirect' => route('peserta.ujian.hasil', $ujian),
            ]);
        }

        // Jika deadline sudah lewat, auto-finalize
        if ($this->deadlinePassed($ujian, $peserta)) {
            $this->autoSubmit($peserta);

            return response()->json([
                'expired' => true,
                'message' => 'Batas waktu ujian telah habis. Ujian otomatis diselesaikan.',
                'redirect' => route('peserta.ujian.hasil', $ujian),
            ]);
        }

        // Update last_activity_at (tracking inactivity timeout)
        $peserta->forceFill(['last_activity_at' => now()])->save();

        return response()->json([
            'expired' => false,
            'sisa_detik' => $this->sisaDetik($ujian, $peserta),
            'server_time' => now()->timestamp,
        ]);
    }

    public function submit(Request $request, Ujian $ujian): RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if (! $peserta) {
            abort(404);
        }

        // Check activation status for offline participants before submit
        if ($peserta->pesertaOffline && ! $peserta->pesertaOffline->is_active) {
            abort(403, 'Status aktivasi tidak valid untuk menyelesaikan ujian.');
        }

        if ($peserta->status !== 'selesai') {
            $this->scoring->finalize($peserta);
        }

        return redirect()->route('peserta.ujian.hasil', $ujian)
            ->with('success', 'Ujian berhasil diselesaikan.');
    }

    public function hasil(Request $request, Ujian $ujian): View|RedirectResponse
    {
        // Try offline peserta first (after exam submit or after login)
        if ($request->session()->has('offline_peserta_id')) {
            $pesertaOfflineId = $request->session()->get('offline_peserta_id');
            $pesertaOffline = PesertaOffline::with('ujianPeserta')->find($pesertaOfflineId);

            if ($pesertaOffline && $pesertaOffline->ujianPeserta) {
                $peserta = $pesertaOffline->ujianPeserta;

                // If status not selesai yet, redirect to exams
                if ($peserta->status !== 'selesai') {
                    return redirect()->route('peserta.offline.exams');
                }

                $breakdown = $this->scoring->breakdownPerJenis($peserta);
                $breakdownSubJenis = $this->scoring->breakdownPerSubJenis($peserta);

                return view('peserta.ujian.hasil', compact('ujian', 'peserta', 'breakdown', 'breakdownSubJenis'));
            }
        }

        // Fallback: try resolve via standard logic
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if (! $peserta) {
            return redirect()->route('peserta.offline.exams');
        }

        abort_unless($peserta->status === 'selesai', 404);

        $breakdown = $this->scoring->breakdownPerJenis($peserta);
        $breakdownSubJenis = $this->scoring->breakdownPerSubJenis($peserta);

        return view('peserta.ujian.hasil', compact('ujian', 'peserta', 'breakdown', 'breakdownSubJenis'));
    }

    public function pembahasan(Request $request, Ujian $ujian): View|RedirectResponse
    {
        // Try offline peserta first
        if ($request->session()->has('offline_peserta_id')) {
            $pesertaOfflineId = $request->session()->get('offline_peserta_id');
            $pesertaOffline = PesertaOffline::with('ujianPeserta')->find($pesertaOfflineId);

            if ($pesertaOffline && $pesertaOffline->ujianPeserta) {
                $peserta = $pesertaOffline->ujianPeserta;

                if ($peserta->status !== 'selesai') {
                    return redirect()->route('peserta.offline.exams');
                }

                if (! $ujian->tampilkan_hasil) {
                    return redirect()->route('peserta.ujian.hasil', $ujian);
                }

                $ujianSoals = $ujian->ujianSoals()
                    ->with('soal.subIndikator.subJenisUjian', 'jenisUjian')
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->get();

                $jawabanMap = $peserta->jawaban()->get()->keyBy('ujian_soal_id');
                $breakdown = $this->scoring->breakdownPerJenis($peserta);

                return view('peserta.ujian.pembahasan', compact('ujian', 'peserta', 'ujianSoals', 'jawabanMap', 'breakdown'));
            }
        }

        // Fallback
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if (! $peserta) {
            return redirect()->route('peserta.offline.exams');
        }

        abort_unless($peserta->status === 'selesai', 404);

        if (! $ujian->tampilkan_hasil) {
            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian', 'jenisUjian')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $jawabanMap = $peserta->jawaban()->get()->keyBy('ujian_soal_id');
        $breakdown = $this->scoring->breakdownPerJenis($peserta);

        return view('peserta.ujian.pembahasan', compact('ujian', 'peserta', 'ujianSoals', 'jawabanMap', 'breakdown'));
    }

    public function leaderboard(Request $request, Ujian $ujian): View|RedirectResponse
    {
        // Try offline peserta first
        if ($request->session()->has('offline_peserta_id')) {
            $pesertaOfflineId = $request->session()->get('offline_peserta_id');
            $pesertaOffline = PesertaOffline::with('ujianPeserta')->find($pesertaOfflineId);

            if ($pesertaOffline && $pesertaOffline->ujianPeserta) {
                $peserta = $pesertaOffline->ujianPeserta;

                if ($peserta->status !== 'selesai') {
                    return redirect()->route('peserta.offline.exams');
                }

                if (! $ujian->tampilkan_hasil) {
                    return redirect()->route('peserta.ujian.hasil', $ujian);
                }

                $ranking = $this->scoring->rank($ujian);
                $posisi = $this->scoring->positionOf($peserta);
                $this->injectLulusStatus($ranking);

                return view('peserta.ujian.leaderboard', compact('ujian', 'peserta', 'ranking', 'posisi'));
            }
        }

        // Fallback
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if (! $peserta) {
            return redirect()->route('peserta.offline.exams');
        }

        abort_unless($peserta->status === 'selesai', 404);

        if (! $ujian->tampilkan_hasil) {
            return redirect()->route('peserta.ujian.hasil', $ujian);
        }

        $ranking = $this->scoring->rank($ujian);
        $posisi = $this->scoring->positionOf($peserta);
        $this->injectLulusStatus($ranking);

        return view('peserta.ujian.leaderboard', compact('ujian', 'peserta', 'ranking', 'posisi'));
    }

    /**
     * Inject computed lulus status ke setiap peserta di ranking berdasarkan breakdown sub jenis.
     * Peserta lulus hanya jika semua sub jenis ujian lulus.
     */
    private function injectLulusStatus(Collection $ranking): void
    {
        foreach ($ranking as $item) {
            $breakdown = $this->scoring->breakdownPerSubJenis($item);

            if (empty($breakdown)) {
                continue;
            }

            $hasFailed = collect($breakdown)->contains(fn ($row) => $row['lulus'] === false);
            $allPassed = collect($breakdown)->every(fn ($row) => $row['lulus'] === true);

            $item->lulus = $allPassed && ! $hasFailed;
        }
    }

    private function resolvePeserta(Request $request, Ujian $ujian): UjianPeserta|RedirectResponse|null
    {
        // Check current exam attempt (offline atau online)
        if ($request->session()->has('offline_attempt_id')) {
            $attemptId = $request->session()->get('offline_attempt_id');
            $sessionUjianId = $request->session()->get('offline_ujian_id');

            if ($sessionUjianId !== $ujian->id) {
                return redirect()->route('peserta.dashboard');
            }

            return $ujian->peserta()->where('id', $attemptId)->first();
        }

        // Check offline peserta session (untuk akses hasil setelah submit)
        if ($request->session()->has('offline_peserta_id')) {
            $pesertaOfflineId = $request->session()->get('offline_peserta_id');
            $pesertaOffline = PesertaOffline::find($pesertaOfflineId);

            if ($pesertaOffline && $pesertaOffline->ujianPeserta) {
                return $pesertaOffline->ujianPeserta;
            }
        }

        // Check online peserta
        if (! $request->user()) {
            return redirect()->route('home');
        }

        return $ujian->peserta()->where('user_id', $request->user()->id)->first();
    }

    private function sisaDetik(Ujian $ujian, UjianPeserta $peserta): ?int
    {
        $deadline = $peserta->batas_waktu;

        if (! $deadline) {
            if (! $ujian->durasi_ujian || ! $peserta->waktu_mulai) {
                return null;
            }

            $deadline = $peserta->waktu_mulai->copy()->addMinutes($ujian->durasi_ujian);
        }

        return max(0, now()->diffInSeconds($deadline, false));
    }

    private function deadlinePassed(Ujian $ujian, UjianPeserta $peserta): bool
    {
        $sisa = $this->sisaDetik($ujian, $peserta);

        return $sisa !== null && $sisa <= 0;
    }

    private function autoSubmit(UjianPeserta $peserta): void
    {
        if ($peserta->status !== 'selesai') {
            $this->scoring->finalize($peserta);
        }
    }

    public function retry(Request $request, Ujian $ujian): RedirectResponse
    {
        if ($request->session()->has('offline_peserta_id')) {
            $pesertaOfflineId = $request->session()->get('offline_peserta_id');
            $pesertaOffline = PesertaOffline::find($pesertaOfflineId);

            if ($pesertaOffline) {
                $newAttempt = $ujian->peserta()->create([
                    'user_id' => null,
                    'peserta_offline_id' => $pesertaOfflineId,
                    'status' => 'sedang_ujian',
                    'waktu_mulai' => now(),
                ]);

                $request->session()->put([
                    'offline_ujian_id' => $ujian->id,
                    'offline_attempt_id' => $newAttempt->id,
                ]);

                return redirect()->route('peserta.ujian.kerjakan', $ujian);
            }
        }

        return redirect()->route('peserta.dashboard');
    }

    public function riwayat(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse || ! $peserta) {
            return redirect()->route('peserta.dashboard');
        }

        $attempts = $ujian->peserta()
            ->where(function ($q) use ($peserta) {
                if ($peserta->user_id) {
                    $q->where('user_id', $peserta->user_id);
                } else {
                    $q->where('peserta_offline_id', $peserta->peserta_offline_id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('peserta.ujian.riwayat', compact('ujian', 'peserta', 'attempts'));
    }

    public function logoutTemporary(Request $request): RedirectResponse
    {
        $attemptId = $request->session()->get('offline_attempt_id');
        $ujianId = $request->session()->get('offline_ujian_id');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($attemptId && $ujianId) {
            $request->session()->put([
                'offline_attempt_id' => $attemptId,
                'offline_ujian_id' => $ujianId,
            ]);
        }

        return redirect()->route('home');
    }
}
