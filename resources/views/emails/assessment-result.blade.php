<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Assessment Resmi UTB</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        /* Base Styles */
        body { 
            font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            line-height: 1.6; 
            color: #334155; 
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        table { border-collapse: collapse; width: 100%; }
        
        .container { 
            max-width: 600px; 
            margin: 40px auto; 
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        /* Decorative Header Bar */
        .top-accent {
            height: 8px;
            background: linear-gradient(90deg, #2563eb 0%, #7c3aed 100%);
        }
        
        /* Header */
        .header {
            padding: 56px 48px 32px 48px;
            text-align: center;
            background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%);
        }
        
        .logo-box {
            display: block;
            margin: 0 auto 16px auto;
            text-align: center;
        }

        .logo {
            max-height: 85px;
            width: auto;
            display: block;
            margin: 0 auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.06));
        }

        .logo-fallback {
            font-size: 20px;
            font-weight: 900;
            color: #2563eb;
            display: none;
        }

        /* If image fails, show text */
        .logo:not([src]), .logo[src=""] { display: none; }
        .logo:not([src]) + .logo-fallback, .logo[src=""] + .logo-fallback { display: block; }
        
        .badge {
            display: block;
            width: fit-content;
            margin: 0 auto 16px auto;
            padding: 8px 18px;
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2rem;
            border-radius: 100px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        
        /* Content */
        .content { padding: 32px 48px 48px 48px; }
        
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        
        .intro {
            font-size: 15px;
            color: #475569;
            margin-bottom: 32px;
        }
        
        /* Hero Result Card */
        .result-hero {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 28px;
            border: 2px solid #e2e8f0;
            padding: 56px 32px;
            text-align: center;
            margin-bottom: 40px;
            background-image: radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
        }
        
        .score-display {
            font-size: 72px;
            font-weight: 800;
            color: #2563eb;
            margin: 0 0 8px 0;
            line-height: 1;
            letter-spacing: -0.04em;
            text-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
        }
        
        .score-meta {
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 28px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Status Banner */
        .status-pill {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.05em;
            box-shadow: 0 6px 20px -4px rgba(0, 0, 0, 0.08);
        }
        
        .status-pass {
            background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
            color: #15803d;
            border: 2px solid #86efac;
        }
        
        .status-fail {
            background: linear-gradient(135deg, #f3f4f6 0%, #f1f5f9 100%);
            color: #475569;
            border: 2px solid #cbd5e1;
        }
        
        /* Details Table */
        .details-wrapper { margin-bottom: 40px; }
        
        .section-label {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            margin-bottom: 24px;
            display: block;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .info-table td {
            padding: 14px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            font-size: 14px;
        }
        
        .info-table tr:last-child td { border-bottom: none; }
        
        .label { color: #64748b; font-weight: 600; }
        .value { color: #0f172a; font-weight: 700; text-align: right; }
        
        /* Feedback Box */
        .feedback-box {
            padding: 32px;
            border-radius: 20px;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 42px;
            border: 2px solid #e2e8f0;
            background-color: #f8fafc;
            box-shadow: 0 4px 12px -2px rgba(0,0,0,0.04);
        }
        
        .feedback-pass { 
            border-color: #86efac; 
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: #15803d;
        }
        
        /* Signature */
        .signature {
            border-top: 1px solid #f1f5f9;
            padding-top: 36px;
            font-size: 15px;
            color: #475569;
        }
        
        .signature strong { color: #0f172a; font-weight: 700; }
        
        /* Footer */
        .footer {
            background-color: #f8fafc;
            padding: 40px 48px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
        }
        
        .footer p {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
        
        .footer-note { font-style: italic; opacity: 0.8; }
        
        /* Mobile Adjustments */
        @media screen and (max-width: 600px) {
            .container { margin: 0; border-radius: 0; border: none; }
            .content, .header, .footer { padding-left: 24px; padding-right: 24px; }
            .score-display { font-size: 56px; }
            .header h1 { font-size: 24px; }
            .result-hero { padding: 40px 20px; }
            .feedback-box { padding: 24px; }
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="top-accent"></div>
        
        <!-- Header -->
        <div class="header">
            <div class="logo-box">
                <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png" alt="UTB Logo" class="logo">
                <span class="logo-fallback">UTB</span>
            </div>
            <div class="badge">Official Report</div>
            <h1>Hasil Evaluasi Akhir</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Halo, <strong>{{ $candidateAssessment->candidate->name }}</strong>.</p>
            
            <p class="intro">
                Terima kasih telah berpartisipasi dalam proses seleksi Universitas Teknologi Bandung. Tim kami telah selesai meninjau hasil pengerjaan assessment Anda dengan rincian sebagai berikut:
            </p>
            
            <!-- Result Hero -->
            <div class="result-hero">
                <div class="score-display">{{ $candidateAssessment->percentage }}%</div>
                <div class="score-meta">{{ $candidateAssessment->total_score }} / {{ $candidateAssessment->max_score }} Poin Terkumpul</div>
                
                <div class="status-pill {{ $candidateAssessment->result === 'pass' ? 'status-pass' : 'status-fail' }}">
                    {{ $candidateAssessment->result === 'pass' ? 'PASSED / MEMENUHI SYARAT' : 'COMPLETED / SELESAI' }}
                </div>
            </div>

            <!-- Detailed Info -->
            <div class="details-wrapper">
                <span class="section-label">Detail Assessment</span>
                <table class="info-table">
                    <tr>
                        <td class="label">Judul Assessment</td>
                        <td class="value">{{ $candidateAssessment->assessment->title }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tipe Assessment</td>
                        <td class="value">{{ $candidateAssessment->assessment->type ?? 'Umum' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Selesai</td>
                        <td class="value">{{ $candidateAssessment->completed_at ? $candidateAssessment->completed_at->format('d F Y, H:i') : '-' }} WIB</td>
                    </tr>
                </table>
            </div>
            
            <!-- Contextual Feedback -->
            <div class="feedback-box {{ $candidateAssessment->result === 'pass' ? 'feedback-pass' : '' }}">
                @if($candidateAssessment->result === 'pass')
                    <strong>Selamat!</strong> Hasil Anda menunjukkan kualifikasi yang sangat baik. Kami akan segera menghubungi Anda dalam 3-5 hari kerja untuk tahapan selanjutnya.
                @else
                    Terima kasih atas partisipasi Anda. Kami sangat menghargai waktu dan usaha yang Anda berikan. Saat ini hasil Anda tersimpan dalam basis data kami sebagai referensi di masa mendatang.
                @endif
            </div>
            
            <!-- Signature -->
            <div class="signature">
                <p>Demikian informasi ini kami sampaikan. Jika ada hal yang ingin ditanyakan, silakan membalas email ini.</p>
                <p style="margin-top: 24px;">
                    Salam takzim,<br>
                    <strong>Universitas Teknologi Bandung</strong><br>
                </p>
            </div>
        </div>
        
        <!-- Corporate Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Universitas Teknologi Bandung. Seluruh Hak Cipta Dilindungi.</p>
            <p>Jl. Terusan Soekarno-Hatta No.448, Bandung, Jawa Barat.</p>
            <p class="footer-note">Email ini dikirim secara otomatis oleh Sistem Rekrutmen UTB.</p>
        </div>
    </div>
</body>
</html>