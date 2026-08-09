<?php

namespace Database\Seeders;

use App\Models\JenisUjian;
use App\Models\PesertaOffline;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use App\Models\Ujian;
use App\Models\UjianJawaban;
use App\Models\UjianPeserta;
use App\Services\Ujian\ExamAssemblyService;
use App\Services\Ujian\UjianScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimulasiUjianSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Cari atau buat Ujian ID=1
            $ujian = Ujian::find(1);

            if (! $ujian) {
                // Asumsi superadmin id = 1
                $ujian = Ujian::create([
                    'id' => 1,
                    'nama_ujian' => 'Simulasi CPNS 2026 Batch 1',
                    'tipe_ujian' => 'offline_kelas',
                    'jumlah_soal' => 90,
                    'acak_soal' => false,
                    'tampilkan_hasil' => true,
                    'tanggal_ujian' => now()->subMinutes(30),
                    'durasi_ujian' => 100, // 100 menit
                    'batas_keterlambatan' => now()->addMinutes(60),
                    'status' => 'aktif',
                    'dibuat_oleh' => 1,
                    'token_ujian' => 'SIMULASI123',
                ]);
            } else {
                $ujian->update([
                    'jumlah_soal' => 90,
                    'status' => 'aktif',
                    'tanggal_ujian' => now()->subMinutes(30),
                    'durasi_ujian' => 100,
                ]);
            }

            // 2. Buat Struktur Kategori (Jenis -> Sub Jenis -> Sub Indikator)
            // SKD (Jenis Ujian Utama)
            $skd = JenisUjian::firstOrCreate(['nama_jenis_ujian' => 'Seleksi Kompetensi Dasar (SKD)']);

            // Daftarkan SKD ke ujian ini (kalau belum)
            $ujian->jenisUjians()->syncWithoutDetaching([$skd->id => ['passing_grade' => 311]]);

            // Sub Jenis: TWK (30 soal, Benar-Salah)
            $twk = SubJenisUjian::firstOrCreate(
                ['jenis_ujian_id' => $skd->id, 'nama_sub_jenis_ujian' => 'Tes Wawasan Kebangsaan (TWK)'],
                ['sistem_penilaian' => 'benar_salah', 'jumlah_jawaban_pilihan_ganda' => 5, 'nilai_benar' => 5, 'urutan' => 1]
            );
            $twkIndikator = SubIndikator::firstOrCreate([
                'jenis_ujian_id' => $skd->id,
                'sub_jenis_ujian_id' => $twk->id,
                'nama_sub_indikator' => 'Nasionalisme & Sejarah',
            ]);

            // Sub Jenis: TIU (30 soal, Benar-Salah)
            $tiu = SubJenisUjian::firstOrCreate(
                ['jenis_ujian_id' => $skd->id, 'nama_sub_jenis_ujian' => 'Tes Intelegensia Umum (TIU)'],
                ['sistem_penilaian' => 'benar_salah', 'jumlah_jawaban_pilihan_ganda' => 5, 'nilai_benar' => 5, 'urutan' => 2]
            );
            $tiuIndikator = SubIndikator::firstOrCreate([
                'jenis_ujian_id' => $skd->id,
                'sub_jenis_ujian_id' => $tiu->id,
                'nama_sub_indikator' => 'Logika & Numerik',
            ]);

            // Sub Jenis: TKP (30 soal, Poin per Jawaban 1-5)
            $tkp = SubJenisUjian::firstOrCreate(
                ['jenis_ujian_id' => $skd->id, 'nama_sub_jenis_ujian' => 'Tes Karakteristik Pribadi (TKP)'],
                ['sistem_penilaian' => 'tiap_jawaban_ada_poin', 'jumlah_jawaban_pilihan_ganda' => 5, 'nilai_benar' => 0, 'urutan' => 3]
            );
            $tkpIndikator = SubIndikator::firstOrCreate([
                'jenis_ujian_id' => $skd->id,
                'sub_jenis_ujian_id' => $tkp->id,
                'nama_sub_indikator' => 'Pelayanan Publik & Profesionalisme',
            ]);

            // Hapus soal lama yang ada di ujian ini agar tidak duplikat/bentrok
            $ujian->ujianSoals()->delete();

            $soalIdsToAttach = [];

            // 3. Generate 30 Soal TWK
            for ($i = 1; $i <= 30; $i++) {
                $kunci = ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])];
                $soal = Soal::create([
                    'sub_indikator_id' => $twkIndikator->id,
                    'soal' => "<p><strong>Soal TWK {$i}</strong> <span style=\"color:#888; font-size:12px;\">(Sub Indikator ID: {$twkIndikator->id})</span><br> Manakah dari pernyataan berikut yang paling tepat mendeskripsikan implementasi nilai Pancasila dalam kehidupan berbangsa pada konteks sejarah modern?</p>",
                    'opsi_a' => "Pernyataan pengecoh A untuk soal TWK {$i}",
                    'opsi_b' => "Pernyataan pengecoh B untuk soal TWK {$i}",
                    'opsi_c' => "Pernyataan pengecoh C untuk soal TWK {$i}",
                    'opsi_d' => "Pernyataan pengecoh D untuk soal TWK {$i}",
                    'opsi_e' => "Pernyataan pengecoh E untuk soal TWK {$i}",
                    'kunci_jawaban' => $kunci,
                    'nilai_bobot_benar' => 5,
                    'pembuat_soal_id' => 1,
                ]);
                $soalIdsToAttach[] = $soal->id;
            }

            // Generate 30 Soal TIU
            for ($i = 1; $i <= 30; $i++) {
                $kunci = ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])];
                $soal = Soal::create([
                    'sub_indikator_id' => $tiuIndikator->id,
                    'soal' => "<p><strong>Soal TIU {$i}</strong> <span style=\"color:#888; font-size:12px;\">(Sub Indikator ID: {$tiuIndikator->id})</span><br> Jika A = B dan B > C, maka kesimpulan logis yang dapat ditarik berdasarkan deret angka yang diberikan adalah...</p>",
                    'opsi_a' => "Pernyataan logika A untuk soal TIU {$i}",
                    'opsi_b' => "Pernyataan logika B untuk soal TIU {$i}",
                    'opsi_c' => "Pernyataan logika C untuk soal TIU {$i}",
                    'opsi_d' => "Pernyataan logika D untuk soal TIU {$i}",
                    'opsi_e' => "Pernyataan logika E untuk soal TIU {$i}",
                    'kunci_jawaban' => $kunci,
                    'nilai_bobot_benar' => 5,
                    'pembuat_soal_id' => 1,
                ]);
                $soalIdsToAttach[] = $soal->id;
            }

            // Generate 30 Soal TKP
            for ($i = 1; $i <= 30; $i++) {
                // TKP tidak ada kunci, tapi tiap opsi punya bobot 1-5
                $bobots = [1, 2, 3, 4, 5];
                shuffle($bobots);

                $soal = Soal::create([
                    'sub_indikator_id' => $tkpIndikator->id,
                    'soal' => "<p><strong>Soal TKP {$i}</strong> <span style=\"color:#888; font-size:12px;\">(Sub Indikator ID: {$tkpIndikator->id})</span><br> Anda ditugaskan oleh atasan untuk menyelesaikan dokumen penting hari ini juga, namun tiba-tiba anak Anda sakit dan harus dibawa ke rumah sakit. Apa yang Anda lakukan?</p>",
                    'opsi_a' => "Sikap/respon A untuk soal TKP {$i}",
                    'opsi_b' => "Sikap/respon B untuk soal TKP {$i}",
                    'opsi_c' => "Sikap/respon C untuk soal TKP {$i}",
                    'opsi_d' => "Sikap/respon D untuk soal TKP {$i}",
                    'opsi_e' => "Sikap/respon E untuk soal TKP {$i}",
                    'nilai_bobot_a' => $bobots[0],
                    'nilai_bobot_b' => $bobots[1],
                    'nilai_bobot_c' => $bobots[2],
                    'nilai_bobot_d' => $bobots[3],
                    'nilai_bobot_e' => $bobots[4],
                    'pembuat_soal_id' => 1,
                ]);
                $soalIdsToAttach[] = $soal->id;
            }

            // Pasang ke ujian
            app(ExamAssemblyService::class)->addQuestions($ujian, $skd->id, $soalIdsToAttach);

            // 4. Generate 10 Peserta Offline
            $ujian->pesertaOffline()->delete(); // Bersihkan peserta lama jika ada

            $pesertaList = [];
            for ($i = 1; $i <= 10; $i++) {
                $kodePlain = strtoupper(Str::random(6));
                $pesertaList[] = PesertaOffline::create([
                    'ujian_id' => $ujian->id,
                    'nomor_peserta' => 'CPNS-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'nama_peserta' => "Peserta Simulasi $i",
                    'kode_akses' => Hash::make($kodePlain),
                    'kode_akses_plain' => $kodePlain,
                ]);
            }

            // 5. Buat Simulasi 2 Peserta (Peserta 1: Selesai, Peserta 2: Sedang Ujian)
            $ujianSoals = $ujian->ujianSoals()->with('soal')->get();
            $scoringService = app(UjianScoringService::class);

            // Peserta 1 -> Selesai mengerjakan 90 soal
            $peserta1 = $pesertaList[0];
            $attempt1 = UjianPeserta::create([
                'ujian_id' => $ujian->id,
                'status' => 'selesai',
                'waktu_mulai' => now()->subMinutes(90),
                'waktu_selesai' => now()->subMinutes(5),
                'batas_waktu' => now()->subMinutes(90)->addMinutes(100),
            ]);
            $peserta1->update(['ujian_peserta_id' => $attempt1->id]);

            foreach ($ujianSoals as $us) {
                // Pilih jawaban acak untuk disimulasikan
                $jawabanStr = ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])];
                $score = $scoringService->scoreAnswer($us->soal, $jawabanStr);

                UjianJawaban::create([
                    'ujian_peserta_id' => $attempt1->id,
                    'ujian_soal_id' => $us->id,
                    'soal_id' => $us->soal_id,
                    'jenis_ujian_id' => $us->jenis_ujian_id,
                    'jawaban' => $jawabanStr,
                    'nilai' => $score['nilai'],
                    'benar' => $score['benar'],
                ]);
            }
            $scoringService->finalize($attempt1);

            // Peserta 2 -> Sedang ujian (Baru jawab 45 soal)
            $peserta2 = $pesertaList[1];
            $attempt2 = UjianPeserta::create([
                'ujian_id' => $ujian->id,
                'status' => 'sedang_ujian',
                'waktu_mulai' => now()->subMinutes(25), // Baru mulai 25 menit lalu
                'batas_waktu' => now()->subMinutes(25)->addMinutes(100),
            ]);
            $peserta2->update(['ujian_peserta_id' => $attempt2->id]);

            foreach ($ujianSoals->take(45) as $us) {
                $jawabanStr = ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])];
                $score = $scoringService->scoreAnswer($us->soal, $jawabanStr);

                UjianJawaban::create([
                    'ujian_peserta_id' => $attempt2->id,
                    'ujian_soal_id' => $us->id,
                    'soal_id' => $us->soal_id,
                    'jenis_ujian_id' => $us->jenis_ujian_id,
                    'jawaban' => $jawabanStr,
                    'nilai' => $score['nilai'],
                    'benar' => $score['benar'],
                ]);
            }
        });
    }
}
