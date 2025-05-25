<!DOCTYPE html>
<html>
<head>
    <title>Welcome to FuLafia Alumni Portal</title>
</head>
<body>
    <h2>Welcome to FuLafia Alumni Portal!</h2>
    
    <p>Dear {{ $name }},</p>
    
    <p>Welcome to the FuLafia Alumni Portal. Your account has been created successfully.</p>
    
    <h3>Your Login Information:</h3>
    <ul>
        <li><strong>Email:</strong> {{ $email }}</li>
    </ul>
    
    <p><strong>Important:</strong> To complete your profile setup:</p>
    <ol>
        <li>Click the link below to set your password</li>
        <li>Update your email address to your preferred email</li>
        <li>Verify your new email address</li>
    </ol>
    
    <p>Set your password here: <a href="{{ $resetLink }}">{{ $resetLink }}</a></p>
    
    <p>You can log in at: <a href="{{ url('/login') }}">{{ url('/login') }}</a></p>
    
    <p>If you have any questions or need assistance, please contact the Alumni Relations Office.</p>
    
    <p>Best regards,<br>
    FuLafia Alumni Relations Office</p>
</body>
</html> 