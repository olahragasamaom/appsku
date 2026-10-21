<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil Diverifikasi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #374151;
        }
        .success-message {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .success-message p {
            color: #166534;
            margin: 0;
        }
        .details {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #374151;
        }
        .detail-value {
            color: #6b7280;
        }
        .cta-button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Pembayaran Berhasil Diverifikasi</h1>
        </div>

        <div class="content">
            <p class="greeting">Halo {{ $peserta->name }},</p>

            <div class="success-message">
                <p>Pembayaran Anda telah berhasil diverifikasi oleh tim Panritta. Selamat! Paket Anda sekarang aktif dan siap digunakan.</p>
            </div>

            <h3 style="color: #374151; margin-top: 30px;">Detail Pembayaran</h3>
            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">Nomor Pembayaran</span>
                    <span class="detail-value">{{ $pembayaran->nomor_pembayaran }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Paket</span>
                    <span class="detail-value">{{ $paket->nama_paket }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nominal Pembayaran</span>
                    <span class="detail-value">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Durasi Paket</span>
                    <span class="detail-value">{{ $paket->durasi_hari }} hari</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Akses Ujian</span>
                    <span class="detail-value">{{ $paket->kuota_ujian ?? 'Unlimited' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Verifikasi</span>
                    <span class="detail-value">{{ $pembayaran->diverifikasi_pada->translatedFormat('d F Y H:i') }}</span>
                </div>
            </div>

            <p style="color: #374151; margin-top: 20px;">Apa yang bisa Anda lakukan sekarang:</p>
            <ul style="color: #6b7280;">
                <li>Akses semua materi ujian yang tersedia di paket Anda</li>
                <li>Mengerjakan soal latihan dan simulasi CAT</li>
                <li>Melihat analitik dan pembahasan jawaban</li>
                @if($paket->video_pembahasan)
                    <li>Menonton video pembahasan soal</li>
                @endif
                @if($paket->sertifikat)
                    <li>Mendapatkan sertifikat setelah menyelesaikan ujian</li>
                @endif
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('peserta.dashboard') }}" class="cta-button">Mulai Belajar Sekarang</a>
            </div>

            <p style="color: #6b7280; font-size: 14px;">Jika Anda memiliki pertanyaan, silakan hubungi tim support kami melalui email: support@panritta.com</p>
        </div>

        <div class="footer">
            <p><strong>Panritta - Platform Simulasi CAT CPNS</strong></p>
            <p>© {{ date('Y') }} Panritta. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
