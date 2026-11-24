<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
        <img
            src="{{ $photoUrl }}"
            alt="Photo of {{ $alumni->user?->name ?: 'alumni' }}"
            class="rounded-circle"
            style="width: 96px; height: 96px; object-fit: cover;"
        >
        <div>
            <div class="fw-600">{{ $alumni->user?->name ?: '—' }}</div>
            <div class="small text-muted">Matric: {{ $alumni->matric_number ?: '—' }}</div>
            <div class="small text-muted">{{ $alumni->faculty ?: '—' }} · {{ $alumni->department ?: '—' }} · {{ $alumni->year_of_graduation ?: '—' }}</div>
        </div>
    </div>
</div>

<div class="adt-panel">
    <div class="adt-table-wrap">
        <table class="adt-table">
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
</div>
