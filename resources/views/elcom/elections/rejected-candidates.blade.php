@extends('layouts.elcom')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">Rejected Candidates Report</h4>
                        <small class="text-muted">{{ $election->title }} ({{ $election->election_year ?? 'N/A' }})</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-secondary btn-sm">Back</a>
                        <a href="{{ route('elcom.elections.rejected-candidates.print', $election) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-print me-1"></i> Print
                        </a>
                        <a href="{{ route('elcom.elections.rejected-candidates.export', $election) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Total rejected: <strong>{{ $rejected->count() }}</strong></p>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Matric</th>
                                    <th>Office</th>
                                    <th>Rejected At</th>
                                    <th>Screened By</th>
                                    <th>Rejection Reason</th>
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
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No rejected candidates for this election.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
