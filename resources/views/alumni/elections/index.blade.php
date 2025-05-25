@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7" style="margin-left: 200px;">
    <div class="row justify-content-center">
        <div class="col-md-10" style="max-width: 800px;">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Available Elections</h3>
                </div>
                <div class="card-body">
                    @if($elections->isEmpty())
                        <p>No elections are currently available.</p>
                    @else
                        <ul class="list-group">
                            @foreach($elections as $election)
                                <li class="list-group-item">
                                    <div class="fw-bold mb-2">{{ $election->title }}</div>
                                    @if($election->offices->isEmpty())
                                        <div class="text-muted">No offices available for this election.</div>
                                    @else
                                        <ul class="list-group mb-2">
                                            @foreach($election->offices as $office)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>{{ $office->title }}</span>
                                                    <div>
                                                        <a href="{{ route('alumni.elections.expression-of-interest.form', [$election, $office]) }}" class="btn btn-sm btn-success me-1">Express Interest</a>
                                                        <a href="{{ route('alumni.elections.published-candidates', [$election, $office]) }}" class="btn btn-sm btn-outline-primary">View Candidates</a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('alumni.elections.accreditation', $election) }}" class="btn btn-sm btn-info me-1">Accreditation</a>
                                        <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-sm btn-primary me-1">Vote</a>
                                        <a href="{{ route('alumni.elections.results', $election) }}" class="btn btn-sm btn-secondary">Results</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 