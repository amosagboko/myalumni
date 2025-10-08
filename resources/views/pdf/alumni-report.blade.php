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
            <img src="{{ public_path('images/fulafia-logo.jpg') }}" class="logo" alt="FULAFIA">
            <div>
                <div class="fw-bold">Federal University of Lafia</div>
                <div class="small">Alumni Personal Data Registration Form</div>
            </div>
            <img src="{{ public_path('images/alumni-logo.jpg') }}" class="logo" alt="ALUMNI">
        </div>
    </div>

    <div class="card">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Full Name:</span> {{ $user->name }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Gender:</span> {{ ucfirst($user->gender) }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Title:</span> {{ $alumni->title }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Matric No:</span> {{ $alumni->matric_number }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Date of Birth:</span> {{ $alumni->date_of_birth }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">LGA:</span> {{ $alumni->lga }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">State of Origin:</span> {{ $alumni->state }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Nationality:</span> {{ $alumni->nationality }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Contact Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Contact Address:</span> {{ $alumni->contact_address }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Email:</span> {{ $user->email }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Phone/WhatsApp:</span> {{ $alumni->phone_number }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Academic Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Year of Entry:</span> {{ $alumni->year_of_entry }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Year of Graduation:</span> {{ $alumni->year_of_graduation }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Department:</span> {{ $alumni->department }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Faculty:</span> {{ $alumni->faculty }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Qualification Type:</span> {{ $alumni->qualification_type }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Qualification Detail:</span> {{ $alumni->qualification_details }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Professional Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Present Employer:</span> {{ $alumni->present_employer }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Present Post/Designation:</span> {{ $alumni->present_designation }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Professional Bodies:</span> {{ $alumni->professional_bodies }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Additional Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col col-6 mb-1"><span class="fw-bold">Student Responsibilities:</span> {{ $alumni->student_responsibilities }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Hobbies:</span> {{ $alumni->hobbies }}</div>
                <div class="col col-6 mb-1"><span class="fw-bold">Other Relevant Information:</span> {{ $alumni->other_information }}</div>
            </div>
        </div>
    </div>

    <div class="meta">
        Generated on {{ $generatedAt->format('d/m/Y H:i') }}
    </div>
</body>
</html>


