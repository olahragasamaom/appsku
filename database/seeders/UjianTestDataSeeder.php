<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Database\Seeder;

class UjianTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUjian();
        $this->seedPaket();
        $this->seedPesertaAndSubscription();
    }

    private function seedUjian(): void
    {
        $subJenisUjian = SubJenisUjian::where('nama_sub_jenis_ujian', 'TWK')->first();

        if (!$subJenisUjian) {
            echo "SubJenisUjian tidak ditemukan. Pastikan sudah di-seed.\n";

            return;
        }

        $ujianData = [
            [
                'nama_ujian' => 'SKD Test 01 - TWK',
                'deskripsi' => 'Simulasi Ujian SKD dengan fokus TWK',
                'durasi' => 30,
            ],
            [
                'nama_ujian' => 'SKD Test 02 - TIU',
                'deskripsi' => 'Simulasi Ujian SKD dengan fokus TIU',
                'durasi' => 40,
            ],
            [
                'nama_ujian' => 'SKD Test 03 - TKPK',
                'deskripsi' => 'Simulasi Ujian SKD dengan fokus TKPK',
                'durasi' => 25,
            ],
            [
                'nama_ujian' => 'SKD Test 04 - Gabungan',
                'deskripsi' => 'Simulasi Ujian SKD Lengkap (TWK+TIU+TKPK)',
                'durasi' => 95,
            ],
            [
                'nama_ujian' => 'SKD Test 05 - Prediksi',
                'deskripsi' => 'Prediksi Ujian SKD dengan standar CPNS 2024',
                'durasi' => 90,
            ],
            [
                'nama_ujian' => 'SKB Test 01 - Hukum',
                'deskripsi' => 'Simulasi Ujian SKB Bidang Hukum',
                'durasi' => 60,
            ],
            [
                'nama_ujian' => 'SKB Test 02 - Administrasi',
                'deskripsi' => 'Simulasi Ujian SKB Bidang Administrasi Negara',
                'durasi' => 60,
            ],
            [
                'nama_ujian' => 'SKB Test 03 - Keuangan',
                'deskripsi' => 'Simulasi Ujian SKB Bidang Audit/Keuangan',
                'durasi' => 60,
            ],
            [
                'nama_ujian' => 'SKB Test 04 - Ekonomi',
                'deskripsi' => 'Simulasi Ujian SKB Bidang Ekonomi/Analisis Kebijakan',
                'durasi' => 60,
            ],
            [
                'nama_ujian' => 'SKB Test 05 - Sosial',
                'deskripsi' => 'Simulasi Ujian SKB Bidang Kesejahteraan Sosial',
                'durasi' => 60,
            ],
        ];

        foreach ($ujianData as $data) {
            Ujian::create([
                'nama_ujian' => $data['nama_ujian'],
                'tipe_ujian' => 'online_paket',
                'sub_jenis_ujian_id' => $subJenisUjian->id,
                'jumlah_soal' => 50,
                'acak_soal' => true,
                'tampilkan_hasil' => true,
                'tanggal_ujian' => now()->addDays(rand(1, 30)),
                'durasi_ujian' => $data['durasi'],
                'batas_keterlambatan' => 5,
                'akses_member' => true,
                'status' => 'aktif',
                'dibuat_oleh' => 1,
            ]);
        }

        echo "✓ 10 Ujian berhasil di-create\n";
    }

    private function seedPaket(): void
    {
        $paketData = [
            [
                'nama_paket' => 'Paket Dasar - SKD Only',
                'slug' => 'paket-dasar-skd',
                'deskripsi' => 'Akses ke 5 simulasi ujian SKD lengkap',
                'harga' => 99000,
                'durasi_hari' => 30,
                'kuota_ujian' => 5,
                'video_pembahasan' => false,
                'analitik' => true,
                'sertifikat' => false,
            ],
            [
                'nama_paket' => 'Paket Standar - SKD + SKB',
                'slug' => 'paket-standar-skd-skb',
                'deskripsi' => 'Akses ke 5 simulasi SKD dan 5 simulasi SKB',
                'harga' => 199000,
                'durasi_hari' => 60,
                'kuota_ujian' => 10,
                'video_pembahasan' => true,
                'analitik' => true,
                'sertifikat' => true,
            ],
            [
                'nama_paket' => 'Paket Premium - All Access',
                'slug' => 'paket-premium-all',
                'deskripsi' => 'Akses unlimited ke semua ujian dan fitur premium',
                'harga' => 399000,
                'durasi_hari' => 90,
                'kuota_ujian' => null,
                'video_pembahasan' => true,
                'analitik' => true,
                'sertifikat' => true,
            ],
            [
                'nama_paket' => 'Paket Trial - Gratis',
                'slug' => 'paket-trial-gratis',
                'deskripsi' => 'Akses coba-coba ke 2 ujian SKD',
                'harga' => 0,
                'durasi_hari' => 7,
                'kuota_ujian' => 2,
                'video_pembahasan' => false,
                'analitik' => false,
                'sertifikat' => false,
            ],
            [
                'nama_paket' => 'Paket Khusus - SKB Only',
                'slug' => 'paket-khusus-skb',
                'deskripsi' => 'Akses ke 5 simulasi ujian SKB saja',
                'harga' => 149000,
                'durasi_hari' => 45,
                'kuota_ujian' => 5,
                'video_pembahasan' => true,
                'analitik' => true,
                'sertifikat' => true,
            ],
        ];

        foreach ($paketData as $data) {
            Paket::create([
                'nama_paket' => $data['nama_paket'],
                'slug' => $data['slug'],
                'deskripsi' => $data['deskripsi'],
                'harga' => $data['harga'],
                'durasi_hari' => $data['durasi_hari'],
                'kuota_ujian' => $data['kuota_ujian'],
                'video_pembahasan' => $data['video_pembahasan'],
                'analitik' => $data['analitik'],
                'sertifikat' => $data['sertifikat'],
                'is_active' => true,
                'urutan' => 0,
            ]);
        }

        echo "✓ 5 Paket berhasil di-create\n";
    }

    private function seedPesertaAndSubscription(): void
    {
        $pesertaEmails = [
            'peserta1@gmail.com',
            'peserta2@gmail.com',
            'peserta3@gmail.com',
            'peserta4@gmail.com',
            'peserta5@gmail.com',
        ];

        $paket = Paket::all();

        foreach ($pesertaEmails as $index => $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Peserta Test '.($index + 1),
                    'username' => 'peserta'.($index + 1),
                    'password' => bcrypt('password123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_peserta' => true,
                ]
            );

            $selectedPaket = $paket[$index % count($paket)];

            PesertaLangganan::create([
                'user_id' => $user->id,
                'paket_id' => $selectedPaket->id,
                'status' => 'active',
                'mulai_pada' => now(),
                'berakhir_pada' => now()->addDays($selectedPaket->durasi_hari),
                'sisa_kuota_ujian' => $selectedPaket->kuota_ujian,
            ]);

            echo "✓ Peserta {$user->name} berhasil di-create dan subscribe paket {$selectedPaket->nama_paket}\n";
        }
    }
}
