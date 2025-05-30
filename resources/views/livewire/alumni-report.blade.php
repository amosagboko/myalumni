<div>
    <title></title> <!-- Empty title to prevent browser title -->
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}

    @if(!$alumni)
        <div class="container mt-5">
            <div class="alert alert-warning">
                <h4 class="alert-heading">Profile Incomplete</h4>
                <p>Your alumni profile information is not available. Please complete your profile first.</p>
                <hr>
                <p class="mb-0">
                    <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary">Complete Profile</a>
                </p>
            </div>
        </div>
    @else
        <!-- Report Content -->
        <div class="container" style="max-width: 800px; margin: 100px 0 0 300px; padding: 10px;">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-1">
                <img src="{{ asset('images/fulafia-logo.jpg') }}" alt="FULAFIA Logo" style="width: 60px; height: 60px;">
                <div class="text-center">
                    <h2 class="mb-0" style="font-size: 1rem;">Federal University of Lafia</h2>
                    <h3 class="mb-0" style="font-size: 0.9rem;">Alumni Personal Data Registration Form</h3>
                </div>
                <img src="{{ asset('images/alumni-logo.jpg') }}" alt="ALUMNI Logo" style="width: 60px; height: 60px;">
            </div>

            <!-- Personal Information -->
            <div class="card mb-1">
                <div class="card-header bg-white text-dark border-bottom py-0">
                    <h6 class="mb-0" style="font-size: 0.8rem;">Personal Information</h6>
                </div>
                <div class="card-body py-1">
                    <div class="row">
                        <div class="col-md-3 text-center mb-1">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" 
                                 alt="Alumni Photo" 
                                 class="rounded-circle mb-0"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Full Name:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $user->name }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Gender:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ ucfirst($user->gender) }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Title:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->title }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Matriculation Number:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->matric_number }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Date of Birth:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->date_of_birth }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">LGA:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->lga }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">State of Origin:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->state }}</p>
                                </div>
                                <div class="col-md-6 mb-0">
                                    <label class="fw-bold" style="font-size: 0.7rem;">Nationality:</label>
                                    <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->nationality }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-1">
                <div class="card-header bg-white text-dark border-bottom py-0">
                    <h6 class="mb-0" style="font-size: 0.8rem;">Contact Information</h6>
                </div>
                <div class="card-body py-1">
                    <div class="row">
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Contact Address:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->contact_address }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Email:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Phone/WhatsApp:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->phone_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="card mb-1">
                <div class="card-header bg-white text-dark border-bottom py-0">
                    <h6 class="mb-0" style="font-size: 0.8rem;">Academic Information</h6>
                </div>
                <div class="card-body py-1">
                    <div class="row">
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Year of Entry:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->year_of_entry }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Year of Graduation:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->year_of_graduation }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Department:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->department }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Faculty:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->faculty }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Qualification Type:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->qualification_type }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Qualification Detail:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->qualification_details }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="card mb-1">
                <div class="card-header bg-white text-dark border-bottom py-0">
                    <h6 class="mb-0" style="font-size: 0.8rem;">Professional Information</h6>
                </div>
                <div class="card-body py-1">
                    <div class="row">
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Present Employer:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->present_employer }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Present Post/Designation:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->present_designation }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Membership of Professional Bodies:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->professional_bodies }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-1">
                <div class="card-header bg-white text-dark border-bottom py-0">
                    <h6 class="mb-0" style="font-size: 0.8rem;">Additional Information</h6>
                </div>
                <div class="card-body py-1">
                    <div class="row">
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Responsibilities as a Student:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->student_responsibilities }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Hobbies:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->hobbies }}</p>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="fw-bold" style="font-size: 0.7rem;">Other Relevant Information:</label>
                            <p class="mb-0" style="font-size: 0.7rem;">{{ $alumni->other_information }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="row mt-4">
                <div class="col-6">
                    <div class="border-top pt-1">
                        <p class="mb-0" style="font-size: 0.7rem;">Signature of Head Alumni Relations Unit</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top pt-1">
                        <p class="mb-0" style="font-size: 0.7rem;">Date: {{ now()->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-center mt-2">
                <button onclick="window.open('{{ route('alumni.print', ['id' => $alumni->id]) }}', '_blank')" class="btn btn-primary me-2">
                    <i class="fas fa-print me-1"></i> Print Form
                </button>
                <button wire:click="downloadPdf" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Download Form
                </button>
            </div>
        </div>
    @endif

    <!-- Print Styles -->
    <style>
        @media print {
            @page {
                size: A4;
                margin: 5mm !important;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                height: 100% !important;
            }

            body {
                width: 210mm !important;
                min-height: 297mm !important;
            }
            
            nav, header, footer, .navbar, .sidebar, .btn {
                display: none !important;
            }
            
            .container {
                max-width: 100% !important;
                width: 200mm !important;
                margin: 0 auto !important;
                padding: 2mm !important;
                position: relative !important;
            }
            
            .d-flex.justify-content-between {
                margin-bottom: 2mm !important;
                page-break-inside: avoid !important;
            }
            
            .d-flex.justify-content-between img {
                width: 50px !important;
                height: 50px !important;
            }
            
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                margin-bottom: 2mm !important;
            }
            
            .card-header {
                background-color: #fff !important;
                color: #000 !important;
                border-bottom: 1px solid #ddd !important;
                padding: 1mm !important;
            }
            
            .card-body {
                padding: 1mm !important;
            }
            
            img {
                max-width: 100% !important;
                page-break-inside: avoid !important;
            }
            
            .row {
                margin-bottom: 1mm !important;
            }
            
            .mb-0 {
                margin-bottom: 0 !important;
            }
            
            .card-body .row {
                page-break-inside: avoid !important;
            }

            .mt-1 {
                margin-top: 2mm !important;
            }

            .d-flex.justify-content-center.mt-2 {
                display: none !important;
            }
        }
    </style>
</div>