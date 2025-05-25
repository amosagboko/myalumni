@extends('layouts.alumni')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Edit Candidate Documents</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.candidates.update-documents', [$election, $candidate]) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Manifesto -->
                        <div class="mb-4">
                            <label for="manifesto" class="form-label">Manifesto</label>
                            <textarea name="manifesto" 
                                      id="manifesto" 
                                      class="form-control @error('manifesto') is-invalid @enderror" 
                                      rows="10" 
                                      required>{{ old('manifesto', $candidate->manifesto) }}</textarea>
                            @error('manifesto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Describe your candidate's vision, goals, and plans if elected. Maximum 10,000 characters.
                            </div>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="mb-4">
                            <label for="documents" class="form-label">Supporting Documents</label>
                            <input type="file" 
                                   name="documents[]" 
                                   id="documents" 
                                   class="form-control @error('documents.*') is-invalid @enderror" 
                                   multiple 
                                   accept=".pdf,.doc,.docx">
                            @error('documents.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Upload supporting documents (PDF, DOC, DOCX). Maximum file size: 10MB per file.
                            </div>

                            <!-- Existing Documents -->
                            @if($candidate->documents)
                                <div class="mt-3">
                                    <h6>Current Documents</h6>
                                    <div class="list-group">
                                        @foreach(json_decode($candidate->documents) as $document)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="{{ asset('storage/' . $document) }}" 
                                                   target="_blank" 
                                                   class="text-decoration-none">
                                                    <i class="bi bi-file-earmark-text me-2"></i>
                                                    {{ basename($document) }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agent.candidates.show', [$election, $candidate]) }}" 
                               class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Update Documents
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 