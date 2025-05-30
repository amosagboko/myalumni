<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Clearance Form Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
            height: auto;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ asset('images/fulafia-logo.jpg') }}" alt="Left Logo" class="logo">
            <img src="{{ asset('images/alumni-logo.jpg') }}" alt="Right Logo" class="logo">
        </div>
        <div class="title">FEDERAL UNIVERSITY OF LAFIA</div>
        <div class="title">ALUMNI CLEARANCE FORM</div>
    </div>

    <div class="content">
        <!-- Personal Information -->
        <div class="section">
            <div class="section-title">Personal Information</div>
            <div class="field">
                <span class="field-label">Surname:</span>
                <span>{{ $data['surname'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">First Name:</span>
                <span>{{ $data['firstname'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Title:</span>
                <span>{{ $data['title'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Matriculation Number:</span>
                <span>{{ $data['matriculation_number'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Date of Birth:</span>
                <span>{{ $data['date_of_birth'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">LGA:</span>
                <span>{{ $data['lga'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">State of Origin:</span>
                <span>{{ $data['state_of_origin'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Nationality:</span>
                <span>{{ $data['nationality'] }}</span>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="section">
            <div class="section-title">Contact Information</div>
            <div class="field">
                <span class="field-label">Contact Address:</span>
                <span>{{ $data['contact_address'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Email:</span>
                <span>{{ $data['email'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Phone/WhatsApp:</span>
                <span>{{ $data['phone'] }}</span>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="section">
            <div class="section-title">Academic Information</div>
            <div class="field">
                <span class="field-label">Year of Entry:</span>
                <span>{{ $data['year_of_entry'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Year of Graduation:</span>
                <span>{{ $data['year_of_graduation'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Department:</span>
                <span>{{ $data['department'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Faculty:</span>
                <span>{{ $data['faculty'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Qualification Type:</span>
                <span>{{ $data['qualification_type'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Qualification Detail:</span>
                <span>{{ $data['qualification_detail'] }}</span>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="section">
            <div class="section-title">Employment Information</div>
            <div class="field">
                <span class="field-label">Present Employer:</span>
                <span>{{ $data['present_employer'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Present Post/Designation:</span>
                <span>{{ $data['present_post'] }}</span>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="section">
            <div class="section-title">Additional Information</div>
            <div class="field">
                <span class="field-label">Professional Bodies:</span>
                <span>{{ $data['professional_bodies'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Student Responsibilities:</span>
                <span>{{ $data['student_responsibilities'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Hobbies:</span>
                <span>{{ $data['hobbies'] }}</span>
            </div>
            <div class="field">
                <span class="field-label">Additional Information:</span>
                <span>{{ $data['additional_info'] }}</span>
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div>Head, Alumni Relations Unit</div>
        <div>Date: {{ date('d/m/Y') }}</div>
    </div>
</body>
</html> 