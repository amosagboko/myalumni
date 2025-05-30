<!DOCTYPE html>
<html>
<head>
    <title>Alumni Report</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: Arial, sans-serif;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5mm;
            padding: 0 10mm;
        }

        .header img {
            width: 45px;
            height: 45px;
        }

        .header-text {
            text-align: center;
            flex: 1;
            margin: 0 5mm;
        }

        .header-text h2 {
            font-size: 14pt;
            margin: 0;
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
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1mm;
        }

        .col-4 {
            width: 33.33%;
            padding: 0 1mm;
            box-sizing: border-box;
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

        .signature-section {
            margin-top: 5mm;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            padding-top: 5mm;
        }

        .signature-text {
            font-size: 9pt;
            margin: 0;
        }

        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }

        .print-button:hover {
            background-color: #c82333;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button">
        <i class="fas fa-print"></i> Print Form
    </button>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/fulafia-logo.jpg') }}" alt="FULAFIA Logo">
            <div class="header-text">
                <h2>Federal University of Lafia</h2>
                <h3>Alumni Personal Data Registration Form</h3>
            </div>
            <img src="{{ asset('images/alumni-logo.jpg') }}" alt="ALUMNI Logo">
        </div>

        <!-- Personal Information -->
        <div class="section">
            <div class="section-header">Personal Information</div>
            <div class="section-body">
                <div class="row">
                    <div class="col-4" style="text-align: center;">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" 
                             alt="Alumni Photo" 
                             class="avatar">
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Full Name:</div>
                            <div class="field-value">{{ $user->name }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Gender:</div>
                            <div class="field-value">{{ ucfirst($user->gender) }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Title:</div>
                            <div class="field-value">{{ $alumni->title }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Matriculation Number:</div>
                            <div class="field-value">{{ $alumni->matric_number }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">Date of Birth:</div>
                            <div class="field-value">{{ $alumni->date_of_birth }}</div>
                        </div>
                        <div class="field">
                            <div class="field-label">LGA:</div>
                            <div class="field-value">{{ $alumni->lga }}</div>
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
                            <div class="field-value">{{ $alumni->contact_address }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Email:</div>
                            <div class="field-value">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Phone/WhatsApp:</div>
                            <div class="field-value">{{ $alumni->phone_number }}</div>
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
                            <div class="field-value">{{ $alumni->year_of_entry }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Year of Graduation:</div>
                            <div class="field-value">{{ $alumni->year_of_graduation }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Department:</div>
                            <div class="field-value">{{ $alumni->department }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Faculty:</div>
                            <div class="field-value">{{ $alumni->faculty }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Qualification Type:</div>
                            <div class="field-value">{{ $alumni->qualification_type }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Qualification Detail:</div>
                            <div class="field-value">{{ $alumni->qualification_details }}</div>
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
                            <div class="field-value">{{ $alumni->present_employer }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Present Post/Designation:</div>
                            <div class="field-value">{{ $alumni->present_designation }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Membership of Professional Bodies:</div>
                            <div class="field-value">{{ $alumni->professional_bodies }}</div>
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
                            <div class="field-value">{{ $alumni->student_responsibilities }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Hobbies:</div>
                            <div class="field-value">{{ $alumni->hobbies }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="field">
                            <div class="field-label">Other Relevant Information:</div>
                            <div class="field-value">{{ $alumni->other_information }}</div>
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
                <p class="signature-text">Date: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html> 