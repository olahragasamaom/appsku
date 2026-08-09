<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Paket Member
        Paket::firstOrCreate(
            ['slug' => 'paket-gratis-coba'],
            [
                'nama_paket' => 'Paket Trial Gratis',
                'deskripsi' => 'Akses terbatas untuk mencoba sistem CAT CPNS Panritta. Cocok untuk pemula.',
                'harga' => 0,
                'durasi_hari' => 7,
                'kuota_ujian' => 2, // Hanya bisa ikut 2 ujian
                'video_pembahasan' => false,
                'analitik' => false,
                'sertifikat' => false,
                'is_active' => true,
                'urutan' => 1,
            ]
        );

        Paket::firstOrCreate(
            ['slug' => 'paket-premium-skd'],
            [
                'nama_paket' => 'Premium SKD Tuntas',
                'deskripsi' => 'Akses penuh ke seluruh bank soal SKD (TWK, TIU, TKP), pembahasan komplit, dan simulasi tak terbatas.',
                'harga' => 149000,
                'durasi_hari' => 180, // 6 Bulan
                'kuota_ujian' => null, // Unlimited
                'video_pembahasan' => true,
                'analitik' => true,
                'sertifikat' => false,
                'is_active' => true,
                'urutan' => 2,
            ]
        );

        Paket::firstOrCreate(
            ['slug' => 'paket-vip-pejuang-nip'],
            [
                'nama_paket' => 'VIP Pejuang NIP',
                'deskripsi' => 'Persiapan maksimal setahun penuh. Dilengkapi video pembahasan eksklusif mentor, tryout nasional, & e-sertifikat.',
                'harga' => 299000,
                'durasi_hari' => 365, // 1 Tahun
                'kuota_ujian' => null, // Unlimited
                'video_pembahasan' => true,
                'analitik' => true,
                'sertifikat' => true,
                'is_active' => true,
                'urutan' => 3,
            ]
        );

        // 2. Data Member Online
        $memberDummy = [
            ['name' => 'Budi Santoso', 'username' => 'budisantoso', 'email' => 'budi@example.com'],
            ['name' => 'Siti Aminah', 'username' => 'sitiaminah', 'email' => 'siti@example.com'],
            ['name' => 'Ahmad Reza', 'username' => 'ahmadreza', 'email' => 'ahmad@example.com'],
            ['name' => 'Nia Ramadhani', 'username' => 'niaramadhani', 'email' => 'nia@example.com'],
            ['name' => 'Rizky Pratama', 'username' => 'rizkypratama', 'email' => 'rizky@example.com'],
        ];

        foreach ($memberDummy as $m) {
            User::firstOrCreate(
                ['username' => $m['username']],
                [
                    'name' => $m['name'],
                    'email' => $m['email'],
                    'password' => Hash::make('password123'), // Password default
                    'phone' => '0812'.rand(10000000, 99999999),
                    'is_peserta' => true,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
