<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate Verification - FULAFIA Alumni Election</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verification-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }

        .verification-header {
            margin-bottom: 30px;
        }

        .verification-header img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        .verification-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .verification-subtitle {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .verification-status {
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
        }

        .status-valid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-invalid {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .certificate-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
            text-align: left;
        }

        .certificate-details p {
            margin: 10px 0;
            color: #495057;
        }

        .certificate-details strong {
            color: #2c3e50;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <img src="{{ asset('images/alumni-logo.JPG') }}" alt="FULAFIA Alumni Logo">
            <h1 class="verification-title">Certificate Verification</h1>
            <div class="verification-subtitle">Federal University of Lafia Alumni Association</div>
        </div>

        <div class="verification-status {{ $isValid ? 'status-valid' : 'status-invalid' }}">
            <div class="status-icon">
                @if($isValid)
                    <i class="fas fa-check-circle"></i>
                @else
                    <i class="fas fa-times-circle"></i>
                @endif
            </div>
            <h2>{{ $isValid ? 'Valid Certificate' : 'Invalid Certificate' }}</h2>
            @if(!$isValid)
                <p>{{ $message }}</p>
            @endif
        </div>

        @if($isValid)
            <div class="certificate-details">
                <p><strong>Certificate Number:</strong> {{ $certificateNumber }}</p>
                <p><strong>Election:</strong> {{ $election->title }}</p>
                <p><strong>Office:</strong> {{ $office->title }}</p>
                <p><strong>Winner:</strong> {{ $winner->alumni->user->name }}</p>
                <p><strong>Issue Date:</strong> {{ $issueDate }}</p>
            </div>
        @endif

        <a href="{{ route('home') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</body>
</html> 