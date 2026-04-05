<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Assessment Resmi UTB</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        /* Base Styles */
        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif; 
            line-height: 1.7; 
            color: #1e293b; 
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }
        
        table { border-collapse: collapse; width: 100%; }
        
        .container { 
            max-width: 640px; 
            margin: 40px auto; 
            background: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08);
            border: 1px solid #f1f5f9;
        }
        
        /* Header */
        .header {
            padding: 60px 48px 40px 48px;
            text-align: center;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
        }
        
        .logo-box {
            display: block;
            margin: 0 auto 20px auto;
            text-align: center;
        }

        .logo {
            max-height: 85px;
            width: auto;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.06));
        }

        .logo-fallback {
            font-size: 24px;
            font-weight: 900;
            color: #2563eb;
            display: none;
        }

        /* If image fails, show text */
        .logo:not([src]), .logo[src=""] { display: none; }
        .logo:not([src]) + .logo-fallback, .logo[src=""] + .logo-fallback { display: block; }
        
        .header h1 {
            margin: 0;
            font-size: 30px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .subtitle {
            display: block;
            width: fit-content;
            margin: 0 auto 16px auto;
            font-size: 13px;
            color: #3b82f6;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
        
        /* Content */
        .content { padding: 0 50px 50px 50px; }
        
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 18px;
            letter-spacing: -0.02em;
        }
        
        .intro {
            font-size: 16px;
            color: #475569;
            margin-bottom: 35px;
        }
        
        /* Information Card */
        .info-card {
            background-color: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        
        .info-card h3 {
            margin: 0 0 20px 0;
            font-size: 12px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
        }

        .info-card h3::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #f1f5f9;
            margin-left: 15px;
        }
        
        .info-table td {
            padding: 14px 0;
            border-bottom: 1px solid #f8fafc;
        }
        
        .info-table tr:last-child td { border-bottom: none; }
        
        .info-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }
        
        .info-value {
            color: #0f172a;
            font-size: 15px;
            font-weight: 700;
            text-align: right;
        }
        
        /* Token Box */
        .token-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 35px;
            background: #f8fafc;
            border-radius: 24px;
        }
        
        .token-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 15px;
            display: block;
            letter-spacing: 0.15em;
        }
        
        .token-code {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 36px;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: 8px;
            text-shadow: 0 2px 10px rgba(37, 99, 235, 0.1);
        }
        
        /* Button */
        .btn-wrapper { text-align: center; margin-bottom: 45px; }
        
        .btn {
            display: inline-block;
            background: #0f172a;
            color: #ffffff !important;
            padding: 22px 48px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.3);
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 35px -10px rgba(15, 23, 42, 0.4);
            background: #1e293b;
        }
        
        .fallback-link {
            display: block;
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
        
        .fallback-link a { color: #3b82f6; text-decoration: none; font-weight: 600; }
        
        /* Instructions */
        .instruction-box {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            padding: 28px;
            border-radius: 20px;
            margin-bottom: 45px;
        }
        
        .instruction-box h4 {
            margin: 0 0 14px 0;
            font-size: 14px;
            font-weight: 800;
            color: #854d0e;
        }
        
        .instruction-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            color: #713f12;
        }
        
        .instruction-box li { margin-bottom: 8px; }
        
        /* Signature */
        .signature {
            border-top: 1px solid #f1f5f9;
            padding-top: 40px;
            font-size: 15px;
            color: #64748b;
        }
        
        .signature strong { color: #0f172a; font-weight: 700; }
        
        /* Footer */
        .footer {
            background-color: #f8fafc;
            padding: 45px 50px;
            text-align: center;
        }
        
        .footer p {
            margin: 0 0 12px 0;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
            line-height: 1.5;
        }
        
        /* Mobile Adjustments */
        @media screen and (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content, .header, .footer { padding: 40px 24px; }
            .token-code { font-size: 28px; letter-spacing: 4px; }
            .header h1 { font-size: 26px; }
            .btn { width: 100%; box-sizing: border-box; }
        }
    </style>

</head>
<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <div class="logo-box">
                <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png" alt="UTB Logo" class="logo">
                <span class="logo-fallback">UTB</span>
            </div>
            <div class="subtitle">Official Admission</div>
            <h1>Undangan Assessment</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Halo, {{ $candidate->name }}</p>
            
            <p class="intro">
                Terima kasih telah melamar di Universitas Teknologi Bandung. Kami mengundang Anda untuk mengikuti tahapan <strong>Assessment Online</strong> sebagai bagian dari proses seleksi kami.
            </p>
            
            <div class="info-card">
                <h3>Detail Pelaksanaan</h3>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Posisi</td>
                        <td class="info-value">{{ $assessment->title }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Durasi</td>
                        <td class="info-value">{{ $assessment->duration_minutes }} Menit</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jadwal Mulai</td>
                        <td class="info-value">{{ $scheduledAt->format('d M Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td class="info-label">Batas Akses</td>
                        <td class="info-value">{{ $deadline->format('d M Y, H:i') }} WIB</td>
                    </tr>
                </table>
            </div>

            <div class="token-section">
                <span class="token-label">Kode Akses Unik</span>
                <div class="token-code">{{ $candidateAssessment->access_token }}</div>
            </div>
            
            <div class="btn-wrapper">
                <a href="{{ $examUrl }}" class="btn">Mulai Assessment</a>
                <div class="fallback-link">
                    Atau salin tautan berikut: <a href="{{ $examUrl }}">{{ $examUrl }}</a>
                </div>
            </div>
            
            <div class="instruction-box">
                <h4>⚠️ Ketentuan Penting</h4>
                <ul>
                    <li>Gunakan perangkat Laptop/PC dengan browser terbaru (Chrome/Edge).</li>
                    <li>Pastikan Anda berada di lingkungan yang tenang dengan koneksi stabil.</li>
                    <li>Sistem akan mengunci jawaban secara otomatis saat waktu habis.</li>
                </ul>
            </div>
            
            <div class="signature">
                <p>Jika butuh bantuan, tim kami siap membantu melalui balasan email ini.</p>
                <p>Salam hangat,</p>
                <p style="margin-top: 10px;">
                    <strong>Universitas Teknologi Bandung</strong><br>
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Universitas Teknologi Bandung.</p>
            <p>Jl. Terusan Soekarno-Hatta No.448, Bandung, Jawa Barat.</p>
            <p style="opacity: 0.6; font-size: 11px;">Pesan ini dihasilkan secara otomatis, mohon tidak mengirim dokumen ke alamat ini.</p>
        </div>
    </div>
</body>
</html>