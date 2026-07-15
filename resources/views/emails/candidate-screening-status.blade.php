<!DOCTYPE html>
<html>
<head>
    <title>Expression of Interest Status Update</title>
</head>
<body>
    <h2>Expression of Interest Status Update</h2>

    <p>Dear {{ $alumni_name }},</p>

    <p>Your expression of interest for <strong>{{ $office }}</strong> has been <strong>{{ $status }}</strong>.</p>

    @if(!empty($remarks))
        <p><strong>Remarks:</strong> {{ $remarks }}</p>
    @endif

    <p>
        <strong>Screened at:</strong> {{ $screened_at }}<br>
        <strong>Screened by:</strong> {{ $screened_by }}
    </p>

    <p>You can review your application status in the alumni elections portal.</p>
</body>
</html>
