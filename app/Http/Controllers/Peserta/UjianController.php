<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UjianController extends Controller
{
    public function __construct(
        private readonly UjianScoringService $scoring
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

        return redirect()->route('peserta.ujian.kerjakan', $ujian);
    }

    public function kerjakan(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        if (! $peserta || $peserta->status === 'terdaftar') {
            return redirect()->route('peserta.ujian.show', $ujian);
        }

        if ($peserta->status === 'selesai') {
            return redirect()->route('peserta.ujian.hasil', $ujian);
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

        if ($ujian->acak_soal) {
            $ujianSoals = $ujianSoals->shuffle();
        }

        $jawaban = $peserta->jawaban()->pluck('jawaban', 'ujian_soal_id');
        $sisaDetik = $this->sisaDetik($ujian, $peserta);

        return view('peserta.ujian.kerjakan', compact('ujian', 'peserta', 'ujianSoals', 'jawaban', 'sisaDetik'));
    }

    public function saveAnswer(Request $request, Ujian $ujian): JsonResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse || ! $peserta) {
            abort(401);
        }

        abort_unless($peserta->status === 'sedang_ujian', 403);

        $validated = $request->validate([
            'ujian_soal_id' => ['required', 'integer'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
        ]);

        $ujianSoal = $ujian->ujianSoals()->with('soal')->findOrFail($validated['ujian_soal_id']);

        $score = $this->scoring->scoreAnswer($ujianSoal->soal, $validated['jawaban'] ?? null);

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

        return response()->json(['saved' => true]);
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

        if ($peserta->status !== 'selesai') {
            $this->scoring->finalize($peserta);
        }

        return redirect()->route('peserta.ujian.hasil', $ujian)
            ->with('success', 'Ujian berhasil diselesaikan.');
    }

    public function hasil(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        abort_unless($peserta && $peserta->status === 'selesai', 404);

        $breakdown = $this->scoring->breakdownPerJenis($peserta);

        return view('peserta.ujian.hasil', compact('ujian', 'peserta', 'breakdown'));
    }

    public function pembahasan(Request $request, Ujian $ujian): View|RedirectResponse
    {
        $peserta = $this->resolvePeserta($request, $ujian);

        if ($peserta instanceof RedirectResponse) {
            return $peserta;
        }

        abort_unless($peserta && $peserta->status === 'selesai', 404);

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

    private function resolvePeserta(Request $request, Ujian $ujian): UjianPeserta|RedirectResponse|null
    {
        if ($request->session()->has('offline_attempt_id')) {
            $attemptId = $request->session()->get('offline_attempt_id');
            $sessionUjianId = $request->session()->get('offline_ujian_id');

            if ($sessionUjianId !== $ujian->id) {
                return redirect()->route('peserta.dashboard');
            }

            return $ujian->peserta()->where('id', $attemptId)->first();
        }

        if (! $request->user()) {
            return redirect()->route('peserta.login');
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
}
