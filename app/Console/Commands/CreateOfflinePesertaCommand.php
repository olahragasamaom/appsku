<?php

namespace App\Console\Commands;

use App\Models\PesertaOffline;
use App\Models\Ujian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateOfflinePesertaCommand extends Command
{
    protected $signature = 'peserta:create-offline {ujian_id} {--count=10}';

    protected $description = 'Buat peserta offline untuk ujian tertentu';

    public function handle(): void
    {
        $ujianId = $this->argument('ujian_id');
        $count = $this->option('count');

        $ujian = Ujian::find($ujianId);

        if (! $ujian) {
            $this->error("Ujian dengan ID {$ujianId} tidak ditemukan");

            return;
        }

        if (! $ujian->isOffline()) {
            $this->error('Ujian harus berjenis offline_kelas');

            return;
        }

        $this->info("Membuat {$count} peserta offline untuk ujian: {$ujian->nama_ujian}");

        $pesertaList = [];
        $passwords = [];

        for ($i = 1; $i <= $count; $i++) {
            $nomorPeserta = sprintf('%s-%03d', $ujian->id, $i);
            $namaPeserta = "Peserta {$i}";
            $kodeAkses = strtoupper(uniqid('PA'));

            $peserta = PesertaOffline::create([
                'ujian_id' => $ujian->id,
                'nomor_peserta' => $nomorPeserta,
                'nama_peserta' => $namaPeserta,
                'kode_akses' => Hash::make($kodeAkses),
                'kode_akses_plain' => $kodeAkses,
                'is_active' => true,
            ]);

            $pesertaList[] = [
                'No' => $i,
                'Nomor Peserta' => $nomorPeserta,
                'Nama' => $namaPeserta,
                'Kode Akses' => $kodeAkses,
            ];

            $passwords[] = [
                'Nomor Peserta' => $nomorPeserta,
                'Nama' => $namaPeserta,
                'Kode Akses' => $kodeAkses,
            ];
        }

        $this->info('');
        $this->info("✓ Berhasil membuat {$count} peserta offline");
        $this->info('');
        $this->table(
            ['No', 'Nomor Peserta', 'Nama', 'Kode Akses'],
            $pesertaList
        );

        $this->warn('');
        $this->warn('⚠️ PERHATIAN: Simpan kode akses di atas dengan aman!');
        $this->warn('Kode akses tidak akan ditampilkan lagi setelah ini.');
        $this->warn('');
    }
}
