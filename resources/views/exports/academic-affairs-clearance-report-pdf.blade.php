<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Affairs Clearance Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 16mm 12mm 16mm 12mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #111827;
            margin: 0;
        }
        .report-header {
            border-bottom: 2px solid #132977;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            color: #132977;
            margin: 0 0 4px;
        }
        .report-meta {
            font-size: 8.5pt;
            color: #4b5563;
            margin: 0 0 2px;
        }
        .filters {
            margin: 8px 0 12px;
            padding: 6px 8px;
            background: #f8fafc;
            border: 0.5pt solid #e5e7eb;
        }
        .filters strong {
            color: #132977;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            display: table-header-group;
        }
        th {
            background: #132977;
            color: #ffffff;
            font-size: 8pt;
            text-align: left;
            padding: 6px 5px;
            border: 0.4pt solid #132977;
        }
        td {
            padding: 5px;
            border: 0.4pt solid #d1d5db;
            font-size: 8pt;
            vertical-align: top;
        }
        tbody tr:nth-child(even) td {
            background: #f9fafb;
        }
        .empty {
            text-align: center;
            padding: 18px;
            color: #6b7280;
        }
        .report-footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 0.4pt solid #d1d5db;
            font-size: 8pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <p class="report-title">Academic Affairs Clearance Report</p>
        <p class="report-meta">Federal University of Lafia — Directorate of Linkages and Alumni Affairs</p>
        <p class="report-meta">Generated: {{ $generatedAt->format('M d, Y g:i A') }}</p>
    </div>

    <div class="filters">
        <strong>Filter criteria:</strong>
        @foreach ($filters as $label => $value)
            {{ $label }}: {{ $value }}@if (! $loop->last) &nbsp;|&nbsp; @endif
        @endforeach
        &nbsp;·&nbsp; {{ $rows->count() }} {{ \Illuminate\Support\Str::plural('record', $rows->count()) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 18%;">When</th>
                <th style="width: 18%;">Alumni</th>
                <th style="width: 16%;">Matric</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 14%;">Actor</th>
                <th style="width: 14%;">Reason</th>
                <th style="width: 8%;">Year</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>
                        @if ($row->cleared_at)
                            {{ \Illuminate\Support\Carbon::parse($row->cleared_at)->format('M d, Y g:i A') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $row->alumni_name }}</td>
                    <td>{{ $row->matric_number }}</td>
                    <td>{{ $row->is_cleared ? 'Cleared' : 'Not cleared' }}</td>
                    <td>{{ $row->actor_name ?: '—' }}</td>
                    <td>{{ $reason }}</td>
                    <td>{{ $row->year_of_graduation ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">No alumni found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="report-footer">
        Academic Affairs Clearance Report · Confidential · Generated {{ $generatedAt->format('M d, Y g:i A') }}
    </p>
</body>
</html>
