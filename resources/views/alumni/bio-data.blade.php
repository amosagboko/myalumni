@extends('layouts.alumni')

@section('content')
<div class="container mt-5 pt-5" style="margin-left: 150px; margin-top: 100px !important;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title">Complete Your Bio Data</h3>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-muted">
                        Please complete your bio data to continue with the onboarding process.
                    </div>

                    <form method="POST" action="{{ route('alumni.bio-data.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <select id="title" name="title" class="form-select @error('title') is-invalid @enderror" required>
                                <option value="">Select Title</option>
                                @foreach($titles as $title)
                                    <option value="{{ $title }}" {{ old('title', $alumni->title) == $title ? 'selected' : '' }}>
                                        {{ $title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nationality -->
                        <div class="mb-3">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" value="{{ old('nationality', $alumni->nationality) }}" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contact Address -->
                        <div class="mb-3">
                            <label for="contact_address" class="form-label">Contact Address</label>
                            <textarea class="form-control @error('contact_address') is-invalid @enderror" id="contact_address" name="contact_address" rows="3" required>{{ old('contact_address', $alumni->contact_address) }}</textarea>
                            @error('contact_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $alumni->phone_number) }}" required>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Qualification Type -->
                        <div class="mb-3">
                            <label for="qualification_type" class="form-label">Qualification Type</label>
                            <select id="qualification_type" name="qualification_type" class="form-select @error('qualification_type') is-invalid @enderror" required>
                                <option value="">Select Qualification Type</option>
                                @foreach($qualificationTypes as $type)
                                    <option value="{{ $type }}" {{ old('qualification_type', $alumni->qualification_type) == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('qualification_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Qualification Details -->
                        <div class="mb-3">
                            <label for="qualification_details" class="form-label">Qualification Details</label>
                            <textarea class="form-control @error('qualification_details') is-invalid @enderror" id="qualification_details" name="qualification_details" rows="3" required>{{ old('qualification_details', $alumni->qualification_details) }}</textarea>
                            @error('qualification_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Present Employer -->
                        <div class="mb-3">
                            <label for="present_employer" class="form-label">Present Employer</label>
                            <input type="text" class="form-control @error('present_employer') is-invalid @enderror" id="present_employer" name="present_employer" value="{{ old('present_employer', $alumni->present_employer) }}" required>
                            @error('present_employer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Present Designation -->
                        <div class="mb-3">
                            <label for="present_designation" class="form-label">Present Designation</label>
                            <input type="text" class="form-control @error('present_designation') is-invalid @enderror" id="present_designation" name="present_designation" value="{{ old('present_designation', $alumni->present_designation) }}" required>
                            @error('present_designation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Professional Bodies -->
                        <div class="mb-3">
                            <label for="professional_bodies" class="form-label">Professional Bodies (Optional)</label>
                            <textarea class="form-control @error('professional_bodies') is-invalid @enderror" id="professional_bodies" name="professional_bodies" rows="2">{{ old('professional_bodies', $alumni->professional_bodies) }}</textarea>
                            @error('professional_bodies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Student Responsibilities -->
                        <div class="mb-3">
                            <label for="student_responsibilities" class="form-label">Student Responsibilities (Optional)</label>
                            <textarea class="form-control @error('student_responsibilities') is-invalid @enderror" id="student_responsibilities" name="student_responsibilities" rows="2">{{ old('student_responsibilities', $alumni->student_responsibilities) }}</textarea>
                            @error('student_responsibilities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hobbies -->
                        <div class="mb-3">
                            <label for="hobbies" class="form-label">Hobbies (Optional)</label>
                            <textarea class="form-control @error('hobbies') is-invalid @enderror" id="hobbies" name="hobbies" rows="2">{{ old('hobbies', $alumni->hobbies) }}</textarea>
                            @error('hobbies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Other Information -->
                        <div class="mb-3">
                            <label for="other_information" class="form-label">Other Information (Optional)</label>
                            <textarea class="form-control @error('other_information') is-invalid @enderror" id="other_information" name="other_information" rows="2">{{ old('other_information', $alumni->other_information) }}</textarea>
                            @error('other_information')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save and Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 