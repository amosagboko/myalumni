<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment statement — {{ $alumni->user?->name ?: $alumni->matric_number }}</title>
    <style>
        body {
            margin: 0;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        .sheet {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 20px;
        }
        .meta {
            margin: 0 0 16px;
            color: #444;
        }
        .identity {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }
        .identity img {
            width: 96px;
            height: 96px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        .toolbar {
            margin-bottom: 16px;
        }
        .toolbar button,
        .toolbar a {
            font: inherit;
            margin-right: 8px;
        }
        @media print {
            .toolbar {
                display: none;
            }
            .sheet {
                max-width: none;
                padding: 0;
            }
            @page {
                size: A4;
                margin: 16mm;
            }
        }
    </style>
</head>
<body>
    <div class="sheet accountant-print">
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print</button>
            <a href="{{ route('accountant-linkages.payments.show', $alumni) }}">Back to view</a>
        </div>
        <h1>Alumni payment statement</h1>
        <p class="meta">Fees and transactions from 2026 onward. Overall status: {{ $status }}.</p>
        <div class="identity">
            <img src="{{ $photoUrl }}" alt="Photo of {{ $alumni->user?->name ?: 'alumni' }}">
            <div>
                <div><strong>{{ $alumni->user?->name ?: '—' }}</strong></div>
                <div>Matric: {{ $alumni->matric_number ?: '—' }}</div>
                <div>Faculty: {{ $alumni->faculty ?: '—' }}</div>
                <div>Department: {{ $alumni->department ?: '—' }}</div>
                <div>Year: {{ $alumni->year_of_graduation ?: '—' }}</div>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['description'] }}</td>
                        <td>&#8358;{{ $row['amount'] }}</td>
                        <td>{{ $row['reference'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No payments from 2026 onward.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
