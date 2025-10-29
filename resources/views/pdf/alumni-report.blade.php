<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alumni Report</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            line-height: 1.2;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 5mm;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 5mm;
            padding: 0 10mm;
        }

        .header-row {
            display: table-row;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-cell.logo {
            width: 45px;
            text-align: left;
        }

        .header-cell.text {
            width: auto;
            text-align: center;
        }

        .header img {
            width: 45px;
            height: 45px;
        }

        .header-text h2 {
            font-size: 14pt;
            margin: 0;
            font-weight: bold;
        }

        .header-text h3 {
            font-size: 12pt;
            margin: 0;
        }

        .section {
            margin-bottom: 3mm;
            border: 0.5pt solid #000;
        }

        .section-header {
            background-color: #f8f9fa;
            padding: 1mm;
            border-bottom: 0.5pt solid #000;
            font-weight: bold;
            font-size: 11pt;
        }

        .section-body {
            padding: 2mm;
        }

        .row {
            display: table;
            width: 100%;
            margin: 0 0 2mm 0;
        }

        .col-4 {
            display: table-cell;
            width: 33.33%;
            padding: 0 1mm;
            vertical-align: top;
        }

        .field {
            margin-bottom: 1mm;
        }

        .field-label {
            font-weight: bold;
            font-size: 9pt;
        }

        .field-value {
            font-size: 9pt;
        }

        .avatar-container {
            text-align: center;
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        .signature-section {
            margin-top: 5mm;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 45%;
            padding-top: 5mm;
        }

        .signature-text {
            font-size: 9pt;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-row">
                <div class="header-cell logo">
                    @php
                        $fulafiaLogo = public_path('images/fulafia-logo.jpg');
                    @endphp
                    @if(file_exists($fulafiaLogo))
                        <img src="{{ $fulafiaLogo }}" alt="FULAFIA Logo">
                    @endif
                </div>
                <div class="header-cell text">
                    <div class="header-text">
                        <h2>Federal University of Lafia</h2>
                        <h3>Alumni Personal Data Registration Form</h3>
                    </div>
                </div>
                <div class="header-cell logo" style="text-align: right;">
                    @php
                        $alumniLogo = public_path('images/alumni-logo.jpg');
                    @endphp
                    @if(file_exists($alumniLogo))
                        <img src="{{ $alumniLogo }}" alt="ALUMNI Logo">
                    @endif
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="section">
            <div class="section-header">Personal Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4 avatar-container">
                        @php
                            $avatarPath = $user->avatar ? public_path('storage/' . $user->avatar) : public_path('images/default-avatar.png');
                        @endphp
                        @if(file_exists($avatarPath))
                            <img src="{{ $avatarPath }}" alt="Alumni Photo" class="avatar">
                        @endif
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Full Name:</div>
                            <div class="field-value">{{ $user->name ?? 'N/A' }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Gender:</div>
                            <div class="field-value">{{ $user->gender ? ucfirst($user->gender) : 'N/A' }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Title:</div>
                            <div class="field-value">{{ $alumni->title ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Matriculation Number:</div>
                            <div class="field-value">{{ $alumni->matric_number ?? 'N/A' }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Date of Birth:</div>
                            <div class="field-value">{{ $alumni->date_of_birth ?? 'N/A' }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">LGA:</div>
                            <div class="field-value">{{ $alumni->lga ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="section">
            <div class="section-header">Contact Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Contact Address:</div>
                            <div class="field-value">{{ $alumni->contact_address ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Email:</div>
                            <div class="field-value">{{ $user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Phone/WhatsApp:</div>
                            <div class="field-value">{{ $alumni->phone_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="section">
            <div class="section-header">Academic Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Year of Entry:</div>
                            <div class="field-value">{{ $alumni->year_of_entry ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Year of Graduation:</div>
                            <div class="field-value">{{ $alumni->year_of_graduation ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Department:</div>
                            <div class="field-value">{{ $alumni->department ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Faculty:</div>
                            <div class="field-value">{{ $alumni->faculty ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Qualification Type:</div>
                            <div class="field-value">{{ $alumni->qualification_type ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Qualification Detail:</div>
                            <div class="field-value">{{ $alumni->qualification_details ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="section">
            <div class="section-header">Professional Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Present Employer:</div>
                            <div class="field-value">{{ $alumni->present_employer ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Present Post/Designation:</div>
                            <div class="field-value">{{ $alumni->present_designation ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Membership of Professional Bodies:</div>
                            <div class="field-value">{{ $alumni->professional_bodies ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="section">
            <div class="section-header">Additional Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Responsibilities as a Student:</div>
                            <div class="field-value">{{ $alumni->student_responsibilities ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Hobbies:</div>
                            <div class="field-value">{{ $alumni->hobbies ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Other Relevant Information:</div>
                            <div class="field-value">{{ $alumni->other_information ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="signature-text">Signature of Head Alumni Relations Unit</p>
            </div>
            <div class="signature-box">
                <p class="signature-text">Date: {{ $generatedAt->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
