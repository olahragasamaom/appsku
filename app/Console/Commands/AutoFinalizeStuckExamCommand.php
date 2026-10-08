<?php

namespace App\Console\Commands;

use App\Models\UjianPeserta;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Console\Command;

class AutoFinalizeStuckExamCommand extends Command
{
    protected $signature = 'ujian:auto-finalize {--dry-run : Hanya tampilkan tanpa eksekusi}';

    protected $description = 'Auto-finalize UjianPeserta yang stuck sedang_ujian setelah batas_waktu lewat';

    public function handle(UjianScoringService $scoring): void
    {
        $dryRun = $this->option('dry-run');

        // Ambil semua attempt yang status 'sedang_ujian' dengan batas_waktu sudah lewat
        $stuckAttempts = UjianPeserta::where('status', 'sedang_ujian')
            ->whereNotNull('batas_waktu')
            ->where('batas_waktu', '<', now())
            ->with('user', 'pesertaOffline', 'ujian')
            ->get();

        if ($stuckAttempts->isEmpty()) {
            $this->info('✓ Tidak ada ujian yang stuck.');

            return;
        }

        $this->info("Ditemukan {$stuckAttempts->count()} ujian yang stuck:");
        $this->info('');

        $results = [];
        foreach ($stuckAttempts as $attempt) {
            $nama = $attempt->user?->name ?? $attempt->pesertaOffline?->nama_peserta ?? 'Peserta #'.$attempt->id;
            $ujianNama = $attempt->ujian?->nama_ujian ?? '-';
            $batasWaktu = $attempt->batas_waktu?->format('d M Y H:i');
            $lewat = $attempt->batas_waktu?->diffForHumans();

            if ($dryRun) {
                $this->line("  → {$nama} | {$ujianNama} | Batas: {$batasWaktu} ({$lewat})");

                continue;
            }

            try {
                $scoring->finalize($attempt);
                $attempt->update(['auto_submitted' => true]);

                $results[] = [
                    'Peserta' => $nama,
                    'Ujian' => $ujianNama,
                    'Batas Waktu' => $batasWaktu,
                    'Status' => '✓ Finalized',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'Peserta' => $nama,
                    'Ujian' => $ujianNama,
                    'Batas Waktu' => $batasWaktu,
                    'Status' => '✗ Error: '.$e->getMessage(),
                ];
            }
        }

        if ($dryRun) {
            $this->warn('');
            $this->warn('ℹ️ Dry run mode - tidak ada perubahan dilakukan.');
            $this->warn('Jalankan tanpa --dry-run untuk eksekusi.');

            return;
        }

        $this->info('');
        $this->table(
            ['Peserta', 'Ujian', 'Batas Waktu', 'Status'],
            $results
        );

        $successCount = collect($results)->filter(fn ($r) => str_contains($r['Status'], '✓'))->count();
        $this->info('');
        $this->info("✓ Berhasil finalisasi {$successCount} dari {$stuckAttempts->count()} ujian.");
    }
}
