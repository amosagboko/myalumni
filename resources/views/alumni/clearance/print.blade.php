<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Clearance Form</title>
    <link rel="stylesheet" href="{{ asset('css/alumni-clearance-form.css') }}">
</head>
<body class="clearance-form-print-page">
    <button type="button" onclick="window.print()" class="clearance-form-print-trigger no-print">
        <i class="fas fa-print"></i> Print Form
    </button>

    <div class="clearance-form-document">
        @include('alumni.clearance.partials.form-document', ['forPdf' => false])
    </div>
</body>
</html>
