@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-7" style="margin-left: 300px; max-width: 900px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Preview Your Votes - {{ $election->title }}</h3>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Please review your selections carefully. Once confirmed, your votes cannot be changed.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Office</th>
                                    <th>Selected Candidate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedCandidates as $selection)
                                <tr>
                                    <td>
                                        <strong>{{ $selection['office']->title }}</strong>
                                        @if($selection['office']->description)
                                            <br>
                                            <small class="text-muted">{{ $selection['office']->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($selection['candidate']->passport)
                                                <img src="{{ Storage::url($selection['candidate']->passport) }}" 
                                                     alt="Candidate Photo" 
                                                     class="rounded-circle me-2"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ $selection['candidate']->alumni->user->name }}</strong>
                                                @if($selection['candidate']->manifesto)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#manifestoModal{{ $selection['candidate']->id }}">
                                                        View Manifesto
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @if($selection['candidate']->manifesto)
                                    <!-- Manifesto Modal -->
                                    <div class="modal fade" id="manifestoModal{{ $selection['candidate']->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        Manifesto - {{ $selection['candidate']->alumni->user->name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="manifesto-content">
                                                        {!! nl2br(e($selection['candidate']->manifesto)) !!}
                                                    </div>
                                                    @if($selection['candidate']->documents)
                                                        <div class="mt-4">
                                                            <h6>Supporting Documents:</h6>
                                                            <ul class="list-unstyled">
                                                                @foreach($selection['candidate']->documents as $document)
                                                                    <li>
                                                                        <a href="{{ asset('storage/' . $document) }}" 
                                                                            target="_blank" 
                                                                            class="btn btn-sm btn-outline-secondary">
                                                                            <i class="bi bi-file-earmark me-2"></i>
                                                                            View Document
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Your votes will be recorded after confirmation. This action cannot be undone.
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('alumni.elections.vote', $election) }}" class="btn btn-secondary">
                                <i class="bi bi-pencil me-2"></i>Modify Votes
                            </a>
                            <form action="{{ route('alumni.elections.submit-vote', $election) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to submit these votes? This action cannot be undone.')">
                                    <i class="bi bi-check-circle me-2"></i>Confirm & Submit Votes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 