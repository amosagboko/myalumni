<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rejected Candidates - {{ $election->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 20px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()">Print</button>
    </div>

    <h1>Federal University of Lafia — Alumni Elections</h1>
    <h2>Rejected Candidates Report</h2>
    <div class="meta">
        <div><strong>Election:</strong> {{ $election->title }}</div>
        <div><strong>Year:</strong> {{ $election->election_year ?? 'N/A' }}</div>
        <div><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</div>
        <div><strong>Total Rejected:</strong> {{ $rejected->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Matric</th>
                <th>Office</th>
                <th>Rejected At</th>
                <th>Screened By</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rejected as $index => $candidate)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $candidate->alumni?->user?->name ?? '—' }}</td>
                    <td>{{ $candidate->alumni?->matriculation_number ?? '—' }}</td>
                    <td>{{ $candidate->office?->title ?? '—' }}</td>
                    <td>{{ $candidate->screened_at?->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ $candidate->screener?->name ?? '—' }}</td>
                    <td>{{ $candidate->rejection_reason ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No rejected candidates.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
