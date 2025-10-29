<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alumni Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000; }
        .header { text-align: center; margin-bottom: 10px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
        .col { padding: 0 8px; box-sizing: border-box; }
        .col-4 { width: 33.3333%; }
        .col-6 { width: 50%; }
        .card { border: 1px solid #ddd; margin-bottom: 8px; }
        .card-header { background: #f8f9fa; padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: bold; }
        .card-body { padding: 6px 8px; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 4px; }
        .text-center { text-align: center; }
        .small { font-size: 11px; }
        .fw-bold { font-weight: bold; }
        .meta { margin-top: 8px; font-size: 11px; color: #444; }
        img.logo { width: 60px; height: 60px; object-fit: cover; }
        .between { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="between">
            @php
                $fulafiaLogo = public_path('images/fulafia-logo.jpg');
                $alumniLogo = public_path('images/alumni-logo.jpg');
            @endphp
            @if(file_exists($fulafiaLogo))
                <img src="{{ $fulafiaLogo }}" class="logo" alt="FULAFIA">
            @endif
            <div>
                <div class="fw-bold">Federal University of Lafia</div>
                <div class="small">Alumni Personal Data Registration Form</div>
            </div>
            @if(file_exists($alumniLogo))
                <img src="{{ $alumniLogo }}" class="logo" alt="ALUMNI">
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1">
                    <span class="fw-bold">Full Name:</span> {{ $user->name }}
                </div>
                <div class="col col-6 mb-1" style="text-align: right;">
                    @php
                        $avatarPath = $user->avatar ? public_path('storage/' . $user->avatar) : public_path('images/default-avatar.png');
                    @endphp
                    @if(file_exists($avatarPath))
                        <img src="{{ $avatarPath }}" alt="Passport" style="width: 150px; height: 150px; object-fit: cover; border-radius: 6px;">
                    @endif
                </div>
                <div class="col col-6 mb-1"><span class="fw-bold">Gender:</span> {{ $user->gender ? ucfirst($user->gender) : 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Title:</span> {{ $alumni->title ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Matric No:</span> {{ $alumni->matric_number ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Date of Birth:</span> {{ $alumni->date_of_birth ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">LGA:</span> {{ $alumni->lga ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">State of Origin:</span> {{ $alumni->state ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Nationality:</span> {{ $alumni->nationality ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Contact Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Contact Address:</span> {{ $alumni->contact_address ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Email:</span> {{ $user->email ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Phone/WhatsApp:</span> {{ $alumni->phone_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Academic Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Year of Entry:</span> {{ $alumni->year_of_entry ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Year of Graduation:</span> {{ $alumni->year_of_graduation ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Department:</span> {{ $alumni->department ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Faculty:</span> {{ $alumni->faculty ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Qualification Type:</span> {{ $alumni->qualification_type ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Qualification Detail:</span> {{ $alumni->qualification_details ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Professional Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Present Employer:</span> {{ $alumni->present_employer ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Present Post/Designation:</span> {{ $alumni->present_designation ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Professional Bodies:</span> {{ $alumni->professional_bodies ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Additional Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Student Responsibilities:</span> {{ $alumni->student_responsibilities ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Hobbies:</span> {{ $alumni->hobbies ?? 'N/A' }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Other Relevant Information:</span> {{ $alumni->other_information ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="meta">
        Generated on {{ $generatedAt->format('d/m/Y H:i') }}
    </div>
</body>
</html>


