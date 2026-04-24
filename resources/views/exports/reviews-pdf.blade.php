<!DOCTYPE html>
<html>
<head>
    <title>Final Review Report</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #111827;
            font-size: 24px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            color: #6b7280;
            margin: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 12px 10px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 10px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-gray { background-color: #f3f4f6; color: #374151; }
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoDir = public_path('images');
            $logoPath = $logoDir . '/logo-utb.png';
            if (!file_exists($logoPath)) {
                if (!is_dir($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                $context = stream_context_create([
                    'http' => [
                        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
                    ]
                ]);
                @copy('https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png', $logoPath, $context);
            }
        @endphp
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" alt="UTB Logo" style="height: 60px; margin-bottom: 10px;">
        @else
            <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Universitas_Teknologi_Bandung_Logo.png" alt="UTB Logo" style="height: 60px; margin-bottom: 10px;">
        @endif
        <h1>Final Review Report</h1>
        <p>Report Date: {{ date('F d, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30%">Candidate Name</th>
                <th width="20%">Recommendation</th>
                <th width="20%">Review Date</th>
                <th width="30%">Reviewer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->candidateAssessment->candidate->name ?? 'N/A' }}</td>
                    <td>
                        @php
                            $badgeClass = match ($record->recommendation) {
                                'approved' => 'badge-success',
                                'probation' => 'badge-warning',
                                'rejected' => 'badge-danger',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($record->recommendation) }}
                        </span>
                    </td>
                    <td>{{ $record->reviewed_at ? $record->reviewed_at->format('M d, Y H:i') : '-' }}</td>
                    <td>{{ $record->reviewer->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistem Assessment Karyawan Baru &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
