<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianPeserta;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HasilUjianController extends Controller
{
    public function __construct(
        private readonly UjianScoringService $scoring
    ) {}

    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/hasil-ujian
     * 
     * Keterangan:
     * Menampilkan DAFTAR UJIAN yang sedang berlangsung (ongoing).
     * Filter otomatis: status = aktif + waktu sekarang dalam rentang ujian.
     * 
     * Rentang berlangsung:
     * - Mulai: tanggal_ujian
     * - Akhir: batas_keterlambatan (jika ada) atau tanggal_ujian + durasi_ujian
     * 
     * Tampilkan per ujian: jumlah peserta total, sedang ujian, selesai.
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($ujians->map(fn($u) => ['id' => $u->id, 'berlangsung' => $u->nama_ujian])->toArray());
     */
    public function index(): View
    {
        $now = now();

        $ujians = Ujian::where('status', 'aktif')
            ->where('tanggal_ujian', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('batas_keterlambatan', '>=', $now)
                    // batas_keterlambatan kosong: pakai tanggal_ujian + durasi
                    ->orWhereRaw(
                        'DATE_ADD(tanggal_ujian, INTERVAL COALESCE(durasi_ujian, 0) MINUTE) >= ?',
                        [$now]
                    );
            })
            ->with(['subJenisUjian', 'peserta'])
            ->withCount([
                'peserta',
                'peserta as peserta_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
                'peserta as peserta_sedang_count' => fn ($q) => $q->where('status', 'sedang_ujian'),
            ])
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        return view('superadmin.hasil-ujian.index', compact('ujians'));
    }

    /**
     * METHOD: show()
     * ==============
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian} (misal: /superadmin/hasil-ujian/5)
     * 
     * Keterangan:
     * Dashboard RINGKASAN hasil ujian: jumlah peserta, yang selesai, sedang ujian.
     * Tampilkan tab navigasi ke: Live Score, Final Score, Analisis Soal, Daftar Peserta.
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujian' => $ujian->nama_ujian, 'total' => $ujian->peserta_count, 'selesai' => $ujian->peserta_selesai_count]);
     */
    public function show(Ujian $ujian): View
    {
        $ujian->loadCount([
            'peserta',
            'peserta as peserta_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
            'peserta as peserta_sedang_count' => fn ($q) => $q->where('status', 'sedang_ujian'),
        ]);

        // Hitung total peserta terdaftar (offline atau online)
        $totalRegistered = $ujian->pesertaOffline()->count();

        // Peserta belum ujian = terdaftar tapi belum punya UjianPeserta
        $pesertaBelumUjian = max(0, $totalRegistered - $ujian->peserta_count);

        return view('superadmin.hasil-ujian.show', compact(
            'ujian',
            'totalRegistered',
            'pesertaBelumUjian',
        ));
    }

    /**
     * METHOD: liveScore()
     * ===================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/live-score
     * 
     * Keterangan:
     * Menampilkan RANKING REALTIME peserta yang SEDANG UJIAN.
     * Score update otomatis via polling/websocket (jika ada frontend JS).
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['ujian_id' => $ujian->id, 'sedang_ujian' => $ujian->peserta_sedang_count]);
     */
    public function liveScore(Ujian $ujian): View
    {
        return view('superadmin.hasil-ujian.live-score', compact('ujian'));
    }

    /**
     * METHOD: finalScore()
     * ====================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/final-score
     * 
     * Keterangan:
     * Menampilkan RANKING FINAL peserta yang sudah SELESAI UJIAN.
     * Gunakan UjianScoringService::rank() untuk hitung & sort nilai.
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($ranking->map(fn($r) => ['posisi' => $r['rank'], 'nama' => $r['nama'], 'nilai' => $r['total_nilai']])->toArray());
     */
    public function finalScore(Ujian $ujian): View
    {
        $ranking = $this->scoring->rank($ujian);

        return view('superadmin.hasil-ujian.final-score', compact('ujian', 'ranking'));
    }

    /**
     * METHOD: soalAnalysis()
     * ======================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/soal-analysis
     * 
     * Keterangan:
     * ANALISIS STATISTIK per soal:
     * - Berapa peserta yang jawab / belum jawab / salah / benar
     * - Persentase benar per soal
     * - Distribusi jawaban (berapa pilih A, B, C, D, E)
     * 
     * Gunakan untuk identifikasi soal yang sulit / terlalu mudah / bermasalah.
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($statistics->map(fn($s) => ['nomor_soal' => $s['ujian_soal']['urutan'], 'persen_benar' => $s['persen_benar']])->toArray());
     */
    public function soalAnalysis(Ujian $ujian): View
    {
        $ujianSoals = $ujian->ujianSoals()
            ->with(['soal.subIndikator.subJenisUjian', 'jenisUjian'])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        // Hitung statistik jawaban per soal
        $totalPeserta = $ujian->peserta()->where('status', 'selesai')->count();

        $statistics = [];
        foreach ($ujianSoals as $ujianSoal) {
            $jawabanCount = UjianJawaban::where('ujian_soal_id', $ujianSoal->id)
                ->whereHas('ujianPeserta', fn ($q) => $q->where('status', 'selesai'))
                ->selectRaw('jawaban, COUNT(*) as total, SUM(CASE WHEN benar = 1 THEN 1 ELSE 0 END) as benar_count')
                ->groupBy('jawaban')
                ->get()
                ->keyBy('jawaban');

            $totalJawab = $jawabanCount->sum('total');
            $totalBenar = $jawabanCount->sum('benar_count');
            $totalSalah = $totalJawab - $totalBenar;
            $belumJawab = max(0, $totalPeserta - $totalJawab);

            $statistics[$ujianSoal->id] = [
                'ujian_soal' => $ujianSoal,
                'soal' => $ujianSoal->soal,
                'total_jawab' => $totalJawab,
                'total_benar' => $totalBenar,
                'total_salah' => $totalSalah,
                'belum_jawab' => $belumJawab,
                'persen_benar' => $totalJawab > 0 ? round(($totalBenar / $totalJawab) * 100, 1) : 0,
                'distribusi' => $jawabanCount,
            ];
        }

        return view('superadmin.hasil-ujian.soal-analysis', compact('ujian', 'statistics', 'totalPeserta'));
    }

    /**
     * METHOD: pesertaList()
     * =====================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/peserta
     * 
     * Keterangan:
     * DAFTAR PESERTA OFFLINE per ujian dengan STATUS:
     * - Belum Ujian: terdaftar tapi belum ada attempt
     * - Sedang Ujian: dalam progress (status = sedang_ujian)
     * - Selesai: sudah submit (status = selesai)
     * 
     * Tampilkan: nomor peserta, nama, status, waktu mulai, waktu selesai, nilai total, lulus?
     * 
     * Form: Tidak ada (view only)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($pesertaOfflineList->map(fn($p) => ['nomor' => $p['nomor_peserta'], 'status' => $p['status_label']])->toArray());
     */
    public function pesertaList(Ujian $ujian): View
    {
        // Peserta offline yang terdaftar
        $pesertaOfflineList = $ujian->pesertaOffline()
            ->with(['ujianPeserta' => function ($q) use ($ujian) {
                $q->where('ujian_id', $ujian->id);
            }])
            ->orderBy('nomor_peserta')
            ->get()
            ->map(function ($peserta) {
                $attempt = $peserta->ujianPeserta;

                $status = 'belum_ujian';
                $statusLabel = 'Belum Ujian';
                $statusColor = 'secondary';

                if ($attempt) {
                    if ($attempt->status === 'selesai') {
                        $status = 'selesai';
                        $statusLabel = 'Selesai';
                        $statusColor = 'success';
                    } elseif ($attempt->status === 'sedang_ujian') {
                        $status = 'sedang_ujian';
                        $statusLabel = 'Sedang Ujian';
                        $statusColor = 'warning';
                    }
                }

                return [
                    'id' => $peserta->id,
                    'nomor_peserta' => $peserta->nomor_peserta,
                    'nama_peserta' => $peserta->nama_peserta,
                    'is_active' => $peserta->is_active,
                    'attempt' => $attempt,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'waktu_mulai' => $attempt?->waktu_mulai,
                    'waktu_selesai' => $attempt?->waktu_selesai,
                    'total_nilai' => $attempt?->total_nilai,
                    'lulus' => $attempt?->lulus,
                ];
            });

        return view('superadmin.hasil-ujian.peserta-list', compact('ujian', 'pesertaOfflineList'));
    }

    /**
     * METHOD: reviewPeserta()
     * =======================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/peserta/{peserta}/review
     * 
     * Keterangan:
     * REVIEW DETAIL jawaban satu peserta:
     * - Tampilkan per soal: soal text, jawaban user, jawaban benar, poin dapat, pembahasan
     * - Breakdown per sub-jenis (TWK/TIU/TKP): nilai per kategori
     * - Total nilai & status lulus/tidak lulus
     * 
     * Form: Tidak ada (view only)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['peserta_id' => $peserta->id, 'ujian_id' => $ujian->id, 'breakdown' => $breakdown]);
     */
    public function reviewPeserta(Ujian $ujian, UjianPeserta $peserta): View
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        $peserta->load('user', 'pesertaOffline', 'jawaban');

        $ujianSoals = $ujian->ujianSoals()
            ->with('soal.subIndikator.subJenisUjian', 'jenisUjian')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $jawabanMap = $peserta->jawaban->keyBy('ujian_soal_id');
        $breakdown = $this->scoring->breakdownPerJenis($peserta);

        return view('superadmin.hasil-ujian.review-peserta', compact(
            'ujian',
            'peserta',
            'ujianSoals',
            'jawabanMap',
            'breakdown',
        ));
    }

    /**
     * METHOD: forceFinalize()
     * =======================
     * URL Contoh: POST /superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-finalize
     * 
     * Keterangan:
     * Finalisasi ujian peserta SECARA PAKSA (tanpa tunggu timer habis).
     * Gunakan jika peserta stuck "sedang_ujian" & tidak bisa submit manual.
     * 
     * Aksi: Update status peserta jadi "selesai", hitung skor, simpan hasil.
     * 
     * Form: Button di management page (POST/PATCH dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Force finalize peserta '.$peserta->id.' ujian '.$ujian->id);
     */
    public function forceFinalize(Ujian $ujian, UjianPeserta $peserta): RedirectResponse
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        if ($peserta->status === 'selesai') {
            return back()->with('info', 'Peserta sudah selesai ujian.');
        }

        $this->scoring->finalize($peserta);

        $nama = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';

        return back()->with('success', "Ujian {$nama} berhasil difinalisasi.");
    }

    /**
     * METHOD: management()
     * ====================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/management
     * 
     * Keterangan:
     * MONITORING PESERTA SEDANG UJIAN secara realtime:
     * - Daftar peserta dengan status "sedang_ujian"
     * - Sisa waktu ujian per peserta
     * - Jumlah soal yang sudah dijawab
     * - Button: Pause, Resume, Force Stop (untuk supervisor control)
     * 
     * Data update otomatis via AJAX polling ke getManagementData().
     * 
     * Form: Tidak ada (view with action buttons)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($pesertaSedangUjian->map(fn($p) => ['nama' => $p['nama'], 'sisa_waktu' => $p['sisa_detik']])->toArray());
     */
    public function management(Ujian $ujian): View
    {
        $pesertaSedangUjian = $this->getPesertaSedangUjian($ujian);

        return view('superadmin.hasil-ujian.management', compact('ujian', 'pesertaSedangUjian'));
    }

    /**
     * METHOD: getManagementData()
     * ===========================
     * URL Contoh: GET /superadmin/hasil-ujian/{ujian}/management-data (AJAX)
     * 
     * Keterangan:
     * API endpoint JSON untuk auto-refresh management page.
     * Dipanggil setiap 5-10 detik via JavaScript fetch/AJAX.
     * 
     * Response JSON:
     * {
     *   "peserta": [
     *     { id, nama, sisa_detik, jumlah_jawaban, total_soal, ... },
     *     ...
     *   ],
     *   "timestamp": "2026-11-16T10:30:45Z"
     * }
     * 
     * Form: Tidak ada (API only)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Management data fetch for ujian '.$ujian->id);
     */
    public function getManagementData(Ujian $ujian): JsonResponse
    {
        $pesertaSedangUjian = $this->getPesertaSedangUjian($ujian);

        return response()->json([
            'peserta' => $pesertaSedangUjian,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function getPesertaSedangUjian(Ujian $ujian): array
    {
        return $ujian->peserta()
            ->where('status', 'sedang_ujian')
            ->with('user', 'pesertaOffline', 'jawaban')
            ->latest('waktu_mulai')
            ->get()
            ->map(function ($peserta) use ($ujian) {
                $nama = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';
                $nomorPeserta = $peserta->pesertaOffline?->nomor_peserta;

                $sisaDetik = $this->calculateSisaDetik($peserta, $ujian);

                $jumlahJawaban = $peserta->jawaban()
                    ->whereNotNull('jawaban')
                    ->count();
                $totalSoal = $ujian->ujianSoals()->count();

                return [
                    'id' => $peserta->id,
                    'nama' => $nama,
                    'nomor_peserta' => $nomorPeserta,
                    'is_paused' => $peserta->is_paused,
                    'paused_at' => $peserta->paused_at,
                    'sisa_detik_saat_pause' => $peserta->sisa_detik_saat_pause,
                    'sisa_detik' => $sisaDetik,
                    'waktu_mulai' => $peserta->waktu_mulai,
                    'status' => $peserta->status,
                    'jumlah_jawaban' => $jumlahJawaban,
                    'total_soal' => $totalSoal,
                ];
            })
            ->toArray();
    }

    private function calculateSisaDetik(UjianPeserta $peserta, Ujian $ujian): int
    {
        if ($peserta->is_paused) {
            return $peserta->sisa_detik_saat_pause ?? 0;
        }

        $waktuMulai = $peserta->waktu_mulai;
        $durasiUjian = $ujian->durasi_ujian ?? 0;
        $batasWaktu = $ujian->batas_keterlambatan;

        if (! $waktuMulai) {
            return 0;
        }

        $waktuSekarang = now();
        $elapsedSeconds = $waktuMulai->diffInSeconds($waktuSekarang);

        $totalWaktuDetik = $durasiUjian * 60;
        $sisaDetik = max(0, $totalWaktuDetik - $elapsedSeconds);

        if ($batasWaktu && $batasWaktu->isPast()) {
            $sisaDetik = 0;
        }

        return (int) $sisaDetik;
    }

    /**
     * METHOD: togglePause()
     * =====================
     * URL Contoh: PATCH /superadmin/hasil-ujian/{ujian}/peserta/{peserta}/toggle-pause
     * 
     * Keterangan:
     * PAUSE / RESUME ujian peserta.
     * 
     * Jika pause:
     * - Set is_paused = true, simpan sisa_detik_saat_pause
     * - Logout peserta session (disconnect dari ujian)
     * - Peserta bisa login kembali nanti untuk resume
     * 
     * Jika resume (is_paused sudah true):
     * - Set is_paused = false, hapus timestamp pause
     * 
     * Form: Button di management page (PATCH dengan CSRF token)
     *       Input: sisa_detik (dari JS calculation)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Toggle pause peserta '.$peserta->id, ['is_paused' => $peserta->is_paused, 'sisa' => $peserta->sisa_detik_saat_pause]);
     */
    public function togglePause(Ujian $ujian, UjianPeserta $peserta, Request $request): RedirectResponse
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        if ($peserta->status !== 'sedang_ujian') {
            return back()->with('error', 'Hanya peserta yang sedang ujian yang bisa di-pause.');
        }

        $nama = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';

        if ($peserta->is_paused) {
            $peserta->update([
                'is_paused' => false,
                'paused_at' => null,
            ]);

            return back()->with('success', "{$nama} berhasil dilanjutkan ujiannya.");
        } else {
            $sisaDetik = (int) $request->input('sisa_detik', 0);
            $peserta->update([
                'is_paused' => true,
                'paused_at' => now(),
                'sisa_detik_saat_pause' => $sisaDetik,
            ]);

            $this->logoutPesertaSession($peserta);

            return back()->with('success', "{$nama} berhasil dihentikan sementara. Peserta telah logout dan dapat login kembali. Sisa waktu: {$sisaDetik} detik.");
        }
    }

    /**
     * METHOD: forceStop()
     * ===================
     * URL Contoh: DELETE /superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-stop
     * 
     * Keterangan:
     * HENTIKAN UJIAN peserta SECARA PERMANENT (tidak bisa resume).
     * Finalisasi ujian, hitung skor, logout peserta.
     * 
     * Gunakan jika: peserta berlaku curang, gangguan teknis, atau inisiatif supervisor.
     * Peserta TIDAK bisa lanjut ujian setelah ini.
     * 
     * Form: Button di management page (DELETE dengan CSRF token)
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::critical('Force stop ujian - peserta '.$peserta->id.' ujian '.$ujian->id);
     */
    public function forceStop(Ujian $ujian, UjianPeserta $peserta): RedirectResponse
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);

        if ($peserta->status !== 'sedang_ujian') {
            return back()->with('error', 'Hanya peserta yang sedang ujian yang bisa di-stop.');
        }

        $nama = $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta ?? 'Peserta';

        $this->scoring->finalize($peserta);
        $this->logoutPesertaSession($peserta);

        return back()->with('success', "{$nama} berhasil dihentikan. Ujian telah disimpan dan tidak dapat dilanjutkan.");
    }

    private function logoutPesertaSession(UjianPeserta $peserta): void
    {
        if ($peserta->pesertaOffline) {
            Cache::forget("offline_session_{$peserta->pesertaOffline->id}");
        }
    }

}
