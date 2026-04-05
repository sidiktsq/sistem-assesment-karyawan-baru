<!DOCTYPE html>
<html>
<head>
    <title>Candidate Assessments Export</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Candidate Assessments Report</h1>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Candidate</th>
                <th>Assessment</th>
                <th>Scheduled At</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Score</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->candidate->name ?? 'N/A' }}</td>
                    <td>{{ $record->assessment->title ?? 'N/A' }}</td>
                    <td>{{ $record->scheduled_at ? $record->scheduled_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $record->deadline ? $record->deadline->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ ucfirst($record->status) }}</td>
                    <td>
                        @if($record->total_score !== null)
                            {{ $record->total_score }} / {{ $record->max_score }} ({{ $record->percentage }}%)
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ucfirst($record->result ?? 'Pending') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
