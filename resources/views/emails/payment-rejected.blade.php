<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Perlu Diverifikasi Ulang</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .warning-message {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .warning-message p {
            color: #92400e;
            margin: 0;
        }
        .reason-box {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .reason-title {
            font-weight: 600;
            color: #7f1d1d;
            margin-bottom: 10px;
        }
        .reason-text {
            color: #7f1d1d;
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
        .steps {
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .step {
            display: flex;
            margin: 15px 0;
            align-items: flex-start;
        }
        .step-number {
            background-color: #0284c7;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-content {
            color: #1e40af;
        }
        .cta-button {
            display: inline-block;
            background-color: #f59e0b;
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
            <h1>⚠ Pembayaran Perlu Diverifikasi Ulang</h1>
        </div>

        <div class="content">
            <p class="greeting">Halo {{ $peserta->name }},</p>

            <div class="warning-message">
                <p>Pembayaran Anda telah ditinjau oleh tim kami, namun ada beberapa hal yang perlu dikoreksi. Silakan upload bukti pembayaran yang sesuai.</p>
            </div>

            <div class="reason-box">
                <div class="reason-title">Alasan Penolakan:</div>
                <p class="reason-text">{{ $alasan }}</p>
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
            </div>

            <h3 style="color: #374151; margin-top: 30px;">Langkah Selanjutnya</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">Periksa kembali bukti pembayaran Anda (foto harus jelas dan mencakup semua detail)</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">Pastikan nominal pembayaran sesuai dengan harga paket (Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }})</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">Buka halaman pembayaran di Panritta dan upload bukti yang benar</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">Tim kami akan memverifikasi dalam waktu 1-24 jam</div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('peserta.langganan.status', $pembayaran->id) }}" class="cta-button">Upload Bukti Lagi</a>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">Jika Anda memiliki pertanyaan atau kesulitan, silakan hubungi tim support kami:</p>
            <p style="color: #6b7280; font-size: 14px; margin: 0;">📧 support@panritta.com</p>
        </div>

        <div class="footer">
            <p><strong>Panritta - Platform Simulasi CAT CPNS</strong></p>
            <p>© {{ date('Y') }} Panritta. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
