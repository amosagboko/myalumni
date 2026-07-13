<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Clearance Form</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            line-height: 1.2;
        }
        .clearance-form-document { width: 100%; max-width: 210mm; margin: 0 auto; padding: 5mm; }
        .clearance-form-header { display: table; width: 100%; margin-bottom: 5mm; }
        .clearance-form-header__logo { display: table-cell; width: 45px; vertical-align: middle; }
        .clearance-form-header__logo--right { text-align: right; }
        .clearance-form-header__text { display: table-cell; text-align: center; vertical-align: middle; }
        .clearance-form-header__text h2 { font-size: 14pt; margin: 0; font-weight: bold; }
        .clearance-form-header__text h3 { font-size: 12pt; margin: 0; }
        .clearance-form-header img { width: 45px; height: 45px; }
        .clearance-form-section { margin-bottom: 3mm; border: 0.5pt solid #000; }
        .clearance-form-section__header {
            background-color: #f8f9fa;
            padding: 1mm;
            border-bottom: 0.5pt solid #000;
            font-weight: bold;
            font-size: 11pt;
        }
        .clearance-form-section__body { padding: 2mm; }
        .clearance-form-row { display: table; width: 100%; margin: 0 0 2mm 0; }
        .clearance-form-col { display: table-cell; width: 33.33%; padding: 0 1mm; vertical-align: top; }
        .clearance-form-col--avatar { text-align: center; }
        .clearance-form-field { margin-bottom: 1mm; }
        .clearance-form-field__label { font-weight: bold; font-size: 9pt; }
        .clearance-form-field__value { font-size: 9pt; }
        .clearance-form-avatar { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; }
        .clearance-form-signatures { margin-top: 5mm; display: table; width: 100%; }
        .clearance-form-signatures__box { display: table-cell; width: 45%; padding-top: 5mm; }
        .clearance-form-signatures__box p { font-size: 9pt; margin: 0; }
    </style>
</head>
<body>
    <div class="clearance-form-document">
        @include('alumni.clearance.partials.form-document', ['forPdf' => true])
    </div>
</body>
</html>
