@extends('layouts.alumni')

@section('content')
<div class="elections-hub-page elections-eoi-form-page w-100 pe-lg-2">
    <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="fw-600 mb-1">Expression of Interest</h4>
                <p class="text-grey-500 font-xssss mb-0">{{ $election->title }} — {{ $office->title }}</p>
            </div>
            <a href="{{ route('alumni.elections') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather-arrow-left me-1"></i> Back to Elections
            </a>
        </div>

        <div class="card-body p-4 w-100 border-0">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Applicant slots</div>
                        <div class="elections-hub-stat__value fw-600">
                            {{ $remainingSlots }} of {{ $maxCandidates }} remaining
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="elections-hub-stat">
                        <div class="elections-hub-stat__label">Screening fee</div>
                        <div class="elections-hub-stat__value fw-600 text-primary">
                            {{ $screeningFee->formatted_amount }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="elections-eoi-panel mb-4">
                <h6 class="fw-600 font-xssss mb-2">Position details</h6>
                <p class="text-grey-500 font-xssss mb-0">{{ $office->description }}</p>
            </div>

            <form action="{{ route('alumni.elections.expression-of-interest.preview', ['election' => $election, 'office' => $office]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="needs-validation"
                  novalidate>
                @csrf

                <div class="elections-eoi-panel mb-4">
                    <label for="passport" class="form-label fw-600 font-xssss">Passport photograph</label>
                    <input type="file"
                           name="passport"
                           id="passport"
                           accept="image/jpeg,image/png,image/jpg,image/gif"
                           class="form-control"
                           required>
                    <div class="form-text font-xssss">Upload a recent passport photograph (max 2MB).</div>
                    @error('passport')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="elections-eoi-panel mb-4">
                    <label for="manifesto" class="form-label fw-600 font-xssss">Manifesto <span class="text-grey-500">(optional)</span></label>
                    <textarea name="manifesto"
                              id="manifesto"
                              rows="6"
                              class="form-control font-xssss"
                              placeholder="Describe your vision and plans for this position...">{{ old('manifesto') }}</textarea>
                    <div class="form-text font-xssss">If provided, minimum 100 characters.</div>
                    @error('manifesto')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="elections-eoi-panel mb-4">
                    <label for="documents" class="form-label fw-600 font-xssss">Supporting documents <span class="text-grey-500">(optional)</span></label>
                    <input type="file"
                           name="documents[]"
                           id="documents"
                           accept=".pdf,.doc,.docx"
                           multiple
                           class="form-control">
                    <div class="form-text font-xssss">PDF, DOC, or DOCX — max 5MB each.</div>
                    @error('documents')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('documents.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info font-xssss mb-4">
                    <div class="fw-600 mb-2"><i class="feather-info me-1"></i> Before you submit</div>
                    <ul class="mb-0 ps-3">
                        <li>You can only express interest in one position at a time.</li>
                        <li>All required alumni fees must be paid before applying.</li>
                        <li>Your bio data must be complete.</li>
                        <li>Your application will be screened by ELCOM after payment.</li>
                        <li>The screening fee is non-refundable.</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        Preview application <i class="feather-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni-elections-hub.css') }}">
@endpush
