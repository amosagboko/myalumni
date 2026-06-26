@extends('layouts.elcom')

@section('content')
<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('elcom.elections.show', $election) }}" class="btn btn-outline-secondary btn-sm me-3">
                            <i class="fas fa-arrow-left"></i> Back to Election Details
                        </a>
                        <h3 class="card-title mb-0">Screen Candidates - {{ $office->title }}</h3>
                    </div>
                    <span class="badge bg-primary">{{ $election->title }}</span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="screen-stat-card">
                                <div class="screen-stat-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <div class="screen-stat-label">Applicants</div>
                                    <div class="screen-stat-value">{{ $office->getActiveApplicantsCount() }} <span class="text-muted fw-normal">/ {{ $office->max_candidates }}</span></div>
                                    @if($election->canAcceptEoiSubmissions() && !$office->hasAvailableApplicantSlots())
                                        <span class="badge rounded-pill bg-secondary mt-1">EOI full</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="screen-stat-card">
                                <div class="screen-stat-icon bg-warning-subtle text-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div>
                                    <div class="screen-stat-label">Unpaid</div>
                                    <div class="screen-stat-value">{{ $pendingCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="screen-stat-card">
                                <div class="screen-stat-icon bg-info-subtle text-info">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div>
                                    <div class="screen-stat-label">Awaiting screening</div>
                                    <div class="screen-stat-value text-info">{{ $awaitingScreeningCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="screen-stat-card">
                                <div class="screen-stat-icon bg-success-subtle text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <div class="screen-stat-label">Approved</div>
                                    <div class="screen-stat-value text-success">{{ $approvedCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="screen-stat-card">
                                <div class="screen-stat-icon bg-danger-subtle text-danger">
                                    <i class="bi bi-x-circle-fill"></i>
                                </div>
                                <div>
                                    <div class="screen-stat-label">Rejected</div>
                                    <div class="screen-stat-value text-danger">{{ $rejectedCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        @if($canScreen)
                            <div class="alert alert-info mb-0 py-2 flex-grow-1">
                                <i class="bi bi-info-circle me-2"></i>
                                Screening is open. Approve paid applicants or reject to free a slot.
                            </div>
                        @else
                            <div class="alert alert-secondary mb-0 py-2 flex-grow-1">
                                <i class="bi bi-lock me-2"></i>
                                Screening is closed for this election phase — this page is read-only.
                            </div>
                        @endif
                        <a href="{{ route('elcom.elections.rejected-candidates', $election) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Rejected Candidates Report
                        </a>
                    </div>

                    @if($candidates->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No candidates have applied for this office yet.
                        </div>
                    @else
                        <div class="screen-table-wrapper">
                            <div class="table-responsive">
                                <table class="table screen-candidates-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="screen-th-num">#</th>
                                            <th><i class="bi bi-person me-1"></i>Candidate</th>
                                            <th><i class="bi bi-flag me-1"></i>Status</th>
                                            <th><i class="bi bi-credit-card me-1"></i>Payment</th>
                                            <th><i class="bi bi-calendar3 me-1"></i>Submitted</th>
                                            <th><i class="bi bi-folder2-open me-1"></i>Documents</th>
                                            <th class="text-end"><i class="bi bi-sliders me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($candidates as $index => $candidate)
                                        @php
                                            $rowClass = match($candidate->status) {
                                                'approved' => 'screen-row-approved',
                                                'rejected' => 'screen-row-rejected',
                                                'paid_awaiting_screening' => 'screen-row-paid',
                                                default => 'screen-row-pending',
                                            };
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="screen-td-num text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($candidate->passport)
                                                        <img src="{{ asset('storage/' . $candidate->passport) }}"
                                                             alt="Passport"
                                                             class="screen-avatar">
                                                    @else
                                                        <div class="screen-avatar screen-avatar-placeholder">
                                                            <i class="bi bi-person-fill"></i>
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <div class="fw-semibold text-dark">{{ $candidate->alumni->user->name }}</div>
                                                        <small class="text-muted d-block">
                                                            <i class="bi bi-mortarboard me-1"></i>{{ $candidate->alumni->matriculation_number }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($candidate->status)
                                                    @case('pending')
                                                        <span class="badge rounded-pill screen-badge-pending">
                                                            <i class="bi bi-hourglass-split me-1"></i>Pending payment
                                                        </span>
                                                        @break
                                                    @case('paid_awaiting_screening')
                                                        <span class="badge rounded-pill bg-info-subtle text-info">
                                                            <i class="bi bi-credit-card me-1"></i>Paid, awaiting screening
                                                        </span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge rounded-pill screen-badge-approved">
                                                            <i class="bi bi-check-lg me-1"></i>Approved
                                                        </span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge rounded-pill screen-badge-rejected">
                                                            <i class="bi bi-x-lg me-1"></i>Rejected
                                                        </span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($candidate->has_paid_screening_fee)
                                                    <span class="badge rounded-pill screen-badge-paid">
                                                        <i class="bi bi-check-circle me-1"></i>Paid
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill screen-badge-unpaid">
                                                        <i class="bi bi-exclamation-circle me-1"></i>Unpaid
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="screen-date">{{ $candidate->created_at->format('M d, Y') }}</span>
                                                <small class="d-block text-muted">{{ $candidate->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary screen-doc-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#documentsModal{{ $candidate->id }}">
                                                    <i class="bi bi-file-earmark-text me-1"></i>
                                                    View
                                                </button>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex flex-column align-items-end gap-2">
                                                @if($canScreen && $candidate->canBeScreened())
                                                    <div class="btn-group btn-group-sm screen-action-group" role="group">
                                                        @if($candidate->isPaidAwaitingScreening())
                                                            <button type="button"
                                                                    class="btn btn-success"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#approveModal{{ $candidate->id }}">
                                                                <i class="bi bi-check-circle me-1"></i>Approve
                                                            </button>
                                                        @endif
                                                        <button type="button"
                                                                class="btn btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal{{ $candidate->id }}">
                                                            <i class="bi bi-x-circle me-1"></i>Reject
                                                        </button>
                                                    </div>
                                                    @if($candidate->isUnpaidPending())
                                                        <small class="screen-hint text-muted">
                                                            Unpaid — reject to free slot
                                                        </small>
                                                    @endif
                                                @elseif($candidate->status === 'approved')
                                                    @if($canAssignAgents)
                                                        <a href="{{ route('elcom.election-offices.candidates.assign-agent-form', [$election, $office, $candidate]) }}"
                                                           class="btn btn-sm btn-primary screen-doc-btn">
                                                            <i class="bi bi-person-plus me-1"></i>Manage Agent
                                                        </a>
                                                    @elseif($candidate->agent)
                                                        <span class="screen-agent-chip">
                                                            <i class="bi bi-person-badge me-1"></i>{{ $candidate->agent->name }}
                                                        </span>
                                                    @else
                                                        <span class="screen-hint text-muted">
                                                            Agent assignment opens during accreditation
                                                        </span>
                                                    @endif
                                                    @if($canAssignAgents && $candidate->agent)
                                                        <span class="screen-agent-chip">
                                                            <i class="bi bi-person-badge me-1"></i>{{ $candidate->agent->name }}
                                                        </span>
                                                    @endif
                                                @elseif($candidate->status === 'rejected')
                                                    <span class="screen-reject-reason" title="{{ $candidate->rejection_reason }}">
                                                        <i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($candidate->rejection_reason, 40) }}
                                                    </span>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @foreach($candidates as $candidate)
                                        <!-- Documents Modal -->
                                        <div class="modal fade screen-candidate-modal" id="documentsModal{{ $candidate->id }}" tabindex="-1" aria-labelledby="documentsModalLabel{{ $candidate->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                <div class="modal-content screen-modal-content">
                                                    <div class="modal-header screen-modal-header">
                                                        <div>
                                                            <h5 class="modal-title mb-1" id="documentsModalLabel{{ $candidate->id }}">
                                                                <i class="bi bi-folder2-open me-2"></i>Candidate Documents
                                                            </h5>
                                                            <small class="screen-modal-subtitle">{{ $candidate->alumni->user->name }} · {{ $candidate->alumni->matriculation_number }}</small>
                                                        </div>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        <div class="screen-table-wrapper screen-modal-table-wrapper border-0 rounded-0 shadow-none">
                                                            <div class="table-responsive">
                                                                <table class="table screen-candidates-table screen-modal-table align-middle mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="screen-th-num">#</th>
                                                                            <th><i class="bi bi-tag me-1"></i>Document Type</th>
                                                                            <th><i class="bi bi-card-text me-1"></i>Details</th>
                                                                            <th class="text-end"><i class="bi bi-box-arrow-up-right me-1"></i>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="screen-row-pending">
                                                                            <td class="screen-td-num text-muted">1</td>
                                                                            <td>
                                                                                <span class="fw-semibold text-dark">
                                                                                    <i class="bi bi-person-badge me-1 text-primary"></i>Passport Photo
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                @if($candidate->passport)
                                                                                    <img src="{{ asset('storage/' . $candidate->passport) }}"
                                                                                         alt="Passport"
                                                                                         class="screen-modal-passport">
                                                                                @else
                                                                                    <span class="text-muted fst-italic">No passport photo uploaded</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-end">
                                                                                @if($candidate->passport)
                                                                                    <a href="{{ asset('storage/' . $candidate->passport) }}"
                                                                                       class="btn btn-sm btn-outline-primary screen-doc-btn"
                                                                                       target="_blank">
                                                                                        <i class="bi bi-eye me-1"></i>Open
                                                                                    </a>
                                                                                @else
                                                                                    <span class="text-muted">—</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                        @if($candidate->documents && count($candidate->documents) > 0)
                                                                            @foreach($candidate->documents as $docIndex => $document)
                                                                                <tr class="screen-row-approved">
                                                                                    <td class="screen-td-num text-muted">{{ $docIndex + 2 }}</td>
                                                                                    <td>
                                                                                        <span class="fw-semibold text-dark">
                                                                                            <i class="bi bi-file-earmark-text me-1 text-success"></i>Supporting Document
                                                                                        </span>
                                                                                    </td>
                                                                                    <td>
                                                                                        <span class="screen-modal-filename">{{ basename($document) }}</span>
                                                                                    </td>
                                                                                    <td class="text-end">
                                                                                        <a href="{{ asset('storage/' . $document) }}"
                                                                                           class="btn btn-sm btn-outline-primary screen-doc-btn"
                                                                                           target="_blank">
                                                                                            <i class="bi bi-download me-1"></i>Open
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        @else
                                                                            <tr class="screen-row-rejected">
                                                                                <td class="screen-td-num text-muted">2</td>
                                                                                <td>
                                                                                    <span class="fw-semibold text-dark">
                                                                                        <i class="bi bi-file-earmark-text me-1 text-muted"></i>Supporting Documents
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <span class="text-muted fst-italic">No supporting documents uploaded</span>
                                                                                </td>
                                                                                <td class="text-end"><span class="text-muted">—</span></td>
                                                                            </tr>
                                                                        @endif
                                                                        @php
                                                                            $manifestoRowNum = ($candidate->documents && count($candidate->documents) > 0)
                                                                                ? count($candidate->documents) + 2
                                                                                : 3;
                                                                        @endphp
                                                                        <tr class="{{ $candidate->manifesto ? 'screen-row-pending' : 'screen-row-rejected' }}">
                                                                            <td class="screen-td-num text-muted">{{ $manifestoRowNum }}</td>
                                                                            <td>
                                                                                <span class="fw-semibold text-dark">
                                                                                    <i class="bi bi-journal-text me-1 text-warning"></i>Manifesto
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                @if($candidate->manifesto)
                                                                                    <div class="screen-modal-manifesto">{{ $candidate->manifesto }}</div>
                                                                                @else
                                                                                    <span class="text-muted fst-italic">No manifesto submitted</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-end"><span class="text-muted">—</span></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer screen-modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($canScreen && $candidate->isPaidAwaitingScreening())
                                        <!-- Approve Modal -->
                                        <div class="modal fade screen-candidate-modal" id="approveModal{{ $candidate->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('elcom.elections.screen-candidate', [$election, $office, $candidate]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Approve Candidate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="mb-0">Are you sure you want to approve this candidate for the ballot?</p>
                                                            <input type="hidden" name="status" value="approved">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Approve Candidate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($canScreen && $candidate->canBeScreened())
                                        <!-- Reject Modal -->
                                        <div class="modal fade screen-candidate-modal" id="rejectModal{{ $candidate->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('elcom.elections.screen-candidate', [$election, $office, $candidate]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Candidate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($candidate->isUnpaidPending())
                                                                <div class="alert alert-warning py-2 small">
                                                                    This applicant has not paid. Rejecting will free their applicant slot.
                                                                </div>
                                                            @endif
                                                            <div class="mb-3">
                                                                <label for="rejection_reason{{ $candidate->id }}" class="form-label">Reason for Rejection</label>
                                                                <textarea class="form-control" id="rejection_reason{{ $candidate->id }}" name="rejection_reason" rows="3" required></textarea>
                                                                <div class="form-text">Please provide a clear reason for rejection.</div>
                                                            </div>
                                                            <input type="hidden" name="status" value="rejected">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Candidate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .screen-stat-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        padding: 1rem 1.15rem;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .screen-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }
    .screen-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 0.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .screen-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        font-weight: 600;
    }
    .screen-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        color: #212529;
    }

    .screen-table-wrapper {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        overflow: visible;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .screen-table-wrapper .table-responsive {
        overflow-x: auto;
        border-radius: 0.75rem;
    }
    .screen-candidate-modal {
        z-index: 1060;
    }
    .screen-candidates-table thead {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #fff;
    }
    .screen-candidates-table thead th {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.9rem 1rem;
        border: none;
        white-space: nowrap;
        color: #fff !important;
    }
    .screen-candidates-table thead th i {
        color: #fff !important;
    }
    .screen-candidates-table tbody tr {
        border-bottom: 1px solid #f0f2f5;
        transition: background-color 0.15s ease;
    }
    .screen-candidates-table tbody tr:last-child {
        border-bottom: none;
    }
    .screen-candidates-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .screen-candidates-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }
    .screen-row-pending {
        border-left: 4px solid #ffc107;
    }
    .screen-row-approved {
        border-left: 4px solid #198754;
    }
    .screen-row-rejected {
        border-left: 4px solid #dc3545;
        opacity: 0.92;
    }
    .screen-th-num,
    .screen-td-num {
        width: 48px;
        text-align: center;
        font-weight: 600;
    }

    .screen-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
        flex-shrink: 0;
    }
    .screen-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e9ecef;
        color: #6c757d;
        font-size: 1.25rem;
    }

    .screen-badge-pending { background: #fff3cd; color: #856404; font-weight: 600; }
    .screen-badge-approved { background: #d1e7dd; color: #0f5132; font-weight: 600; }
    .screen-badge-rejected { background: #f8d7da; color: #842029; font-weight: 600; }
    .screen-badge-paid { background: #d1e7dd; color: #0f5132; font-weight: 600; }
    .screen-badge-unpaid { background: #f8d7da; color: #842029; font-weight: 600; }

    .screen-date {
        font-weight: 600;
        color: #343a40;
        font-size: 0.9rem;
    }
    .screen-doc-btn {
        border-radius: 0.5rem;
        font-weight: 500;
        white-space: nowrap;
    }
    .screen-action-group .btn {
        border-radius: 0.5rem !important;
        font-weight: 500;
    }
    .screen-hint {
        font-size: 0.78rem;
        max-width: 180px;
        text-align: right;
        line-height: 1.3;
    }
    .screen-agent-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.82rem;
        background: #e7f1ff;
        color: #0d6efd;
        padding: 0.35rem 0.65rem;
        border-radius: 2rem;
        font-weight: 500;
    }
    .screen-reject-reason {
        display: inline-block;
        font-size: 0.82rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 0.4rem 0.65rem;
        border-radius: 0.5rem;
        max-width: 200px;
        text-align: right;
    }

    .screen-modal-content {
        border: none;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }
    .screen-modal-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #fff;
        border-bottom: none;
        padding: 1.1rem 1.25rem;
    }
    .screen-modal-header .modal-title {
        color: #fff;
        font-weight: 600;
    }
    .screen-modal-subtitle {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
    }
    .screen-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e9ecef;
    }
    .screen-modal-table-wrapper {
        margin: 0;
    }
    .screen-modal-passport {
        max-width: 120px;
        max-height: 120px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }
    .screen-modal-filename {
        font-size: 0.9rem;
        color: #343a40;
        word-break: break-all;
    }
    .screen-modal-manifesto {
        font-size: 0.9rem;
        color: #495057;
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        max-height: 160px;
        overflow-y: auto;
        line-height: 1.55;
        white-space: pre-wrap;
    }

    @media (max-width: 991.98px) {
        .screen-candidates-table thead th,
        .screen-candidates-table tbody td {
            padding: 0.75rem 0.65rem;
        }
        .screen-avatar,
        .screen-avatar-placeholder {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.screen-candidate-modal').forEach(function (modalEl) {
            modalEl.addEventListener('show.bs.modal', function () {
                if (modalEl.parentElement && modalEl.parentElement !== document.body) {
                    document.body.appendChild(modalEl);
                }
            });
        });
    });
</script>
@endpush
