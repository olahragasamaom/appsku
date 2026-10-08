<?php

namespace App\Console\Commands;

use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianPeserta;
use App\Services\Ujian\OfflineParticipantService;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulatePesertaExamCommand extends Command
{
    protected $signature = 'peserta:simulate-exam 
                            {ujian_id : ID ujian yang akan disimulasikan}
                            {--peserta-ids=* : ID peserta offline (default: ambil 3 peserta pertama)}
                            {--count=3 : Jumlah peserta untuk disimulasikan jika ids tidak diberikan}
                            {--correct-rate=70 : Persentase jawaban benar (0-100)}';

    protected $description = 'Simulasi penginputan jawaban ujian untuk peserta offline';

    public function handle(
        OfflineParticipantService $participantService,
        UjianScoringService $scoringService
    ): void {
        $ujianId = $this->argument('ujian_id');
        $pesertaIds = $this->option('peserta-ids');
        $count = (int) $this->option('count');
        $correctRate = (int) $this->option('correct-rate');

        $ujian = Ujian::with(['ujianSoals.soal', 'ujianJenisUjians'])->find($ujianId);

        if (! $ujian) {
            $this->error("Ujian dengan ID {$ujianId} tidak ditemukan");

            return;
        }

        if (! $ujian->isOffline()) {
            $this->error('Ujian harus berjenis offline_kelas');

            return;
        }

        // Get peserta
        $pesertaQuery = PesertaOffline::where('ujian_id', $ujianId);

        if (! empty($pesertaIds)) {
            $pesertaQuery->whereIn('id', $pesertaIds);
        } else {
            $pesertaQuery->orderBy('nomor_peserta')->limit($count);
        }

        $pesertaList = $pesertaQuery->get();

        if ($pesertaList->isEmpty()) {
            $this->error('Tidak ada peserta ditemukan');

            return;
        }

        $this->info("Memulai simulasi untuk {$pesertaList->count()} peserta");
        $this->info("Ujian: {$ujian->nama_ujian}");
        $this->info("Jumlah soal: {$ujian->ujianSoals->count()}");
        $this->info("Target benar: {$correctRate}%");
        $this->info('');

        $results = [];

        foreach ($pesertaList as $peserta) {
            $this->info("→ Memproses: {$peserta->nomor_peserta} - {$peserta->nama_peserta}");

            DB::transaction(function () use ($peserta, $ujian, $correctRate, $scoringService, $participantService, &$results) {
                // 1. Aktifkan peserta jika belum
                if (! $peserta->is_active) {
                    $peserta->update(['is_active' => true]);
                    $this->line('  ✓ Peserta diaktifkan');
                }

                // 2. Mark hadir jika belum
                $kehadiran = $peserta->kehadiran()->where('ujian_id', $ujian->id)->first();
                if (! $kehadiran || $kehadiran->status_kehadiran !== 'hadir') {
                    $participantService->markAttendance($peserta, $ujian, 'hadir');
                    $this->line('  ✓ Peserta ditandai hadir');
                }

                // 3. Hapus attempt existing jika ada (untuk re-simulasi)
                if ($peserta->ujian_peserta_id) {
                    UjianJawaban::where('ujian_peserta_id', $peserta->ujian_peserta_id)->delete();
                    UjianPeserta::where('id', $peserta->ujian_peserta_id)->delete();
                    $peserta->update(['ujian_peserta_id' => null]);
                }

                // 4. Buat attempt baru
                $attempt = UjianPeserta::create([
                    'ujian_id' => $ujian->id,
                    'user_id' => null,
                    'status' => 'sedang_ujian',
                    'waktu_mulai' => now()->subMinutes($ujian->durasi_ujian ?? 90),
                    'batas_waktu' => now()->addMinutes(10),
                ]);

                $peserta->update(['ujian_peserta_id' => $attempt->id]);

                // 5. Simulasi jawaban untuk setiap soal
                $totalSoal = $ujian->ujianSoals->count();
                $benarCount = 0;
                $salahCount = 0;

                foreach ($ujian->ujianSoals as $ujianSoal) {
                    $soal = $ujianSoal->soal;

                    if (! $soal) {
                        continue;
                    }

                    // Tentukan jawaban berdasarkan correctRate
                    $shouldBeCorrect = rand(1, 100) <= $correctRate;

                    if ($shouldBeCorrect && $soal->kunci_jawaban) {
                        $jawaban = $soal->kunci_jawaban;
                    } else {
                        // Pilih jawaban random (bukan kunci jika salah)
                        $options = ['A', 'B', 'C', 'D', 'E'];
                        if ($soal->kunci_jawaban) {
                            $options = array_diff($options, [$soal->kunci_jawaban]);
                        }
                        $jawaban = $options[array_rand($options)];
                    }

                    $scoring = $scoringService->scoreAnswer($soal, $jawaban);

                    UjianJawaban::create([
                        'ujian_peserta_id' => $attempt->id,
                        'ujian_soal_id' => $ujianSoal->id,
                        'soal_id' => $soal->id,
                        'jenis_ujian_id' => $ujianSoal->jenis_ujian_id,
                        'jawaban' => $jawaban,
                        'nilai' => $scoring['nilai'],
                        'benar' => $scoring['benar'],
                    ]);

                    if ($scoring['benar'] === true) {
                        $benarCount++;
                    } else {
                        $salahCount++;
                    }
                }

                // 6. Finalize attempt (hitung nilai total & lulus/tidak)
                $attempt->refresh();
                $scoringService->finalize($attempt);

                $attempt->refresh();

                $results[] = [
                    'Nomor' => $peserta->nomor_peserta,
                    'Nama' => $peserta->nama_peserta,
                    'Total Soal' => $totalSoal,
                    'Benar' => $benarCount,
                    'Salah' => $salahCount,
                    'Nilai' => number_format($attempt->total_nilai, 2),
                    'Lulus' => $attempt->lulus ? '✓ Lulus' : '✗ Tidak Lulus',
                ];

                $this->line("  ✓ {$totalSoal} jawaban diinput (Benar: {$benarCount}, Salah: {$salahCount})");
                $this->line('  ✓ Nilai: '.number_format($attempt->total_nilai, 2));
            });
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════');
        $this->info('  HASIL SIMULASI');
        $this->info('═══════════════════════════════════════════════');
        $this->table(
            ['Nomor', 'Nama', 'Total Soal', 'Benar', 'Salah', 'Nilai', 'Lulus'],
            $results
        );

        $this->info('');
        $this->info('✓ Simulasi selesai!');
    }
}
