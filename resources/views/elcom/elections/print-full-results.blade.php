<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Full Election Results - {{ $election->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        function printDocument() {
            window.print();
        }
    </script>
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
            padding-top: 20px;
        }
        .header-logo {
            width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 20px;
            font-weight: normal;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .header-divider {
            width: 100px;
            height: 3px;
            background-color: #007bff;
            margin: 15px auto;
        }
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .stats p {
            margin: 5px 0;
        }
        .office-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .office-title {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .candidate-list {
            margin-left: 20px;
        }
        .candidate-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
        }
        .candidate-item h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .candidate-stats {
            display: flex;
            justify-content: space-between;
            color: #666;
        }
        .signature-section {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-box {
            width: 200px;
            margin-top: 50px;
            text-align: center;
            float: left;
            margin-right: 50px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        .clear {
            clear: both;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .footer {
                position: fixed;
                bottom: 0;
            }
        }
        button {
            transition: all 0.2s ease;
        }
        button:hover {
            background: #0056b3 !important;
            transform: translateY(-1px);
        }
        button:active {
            transform: translateY(0);
        }
        .print-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .print-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white !important;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 150px;
        }
        .print-button:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .print-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-button i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="print-container no-print">
        <a href="javascript:void(0)" onclick="printDocument()" class="print-button">
            <i class="fas fa-print"></i>Print Results
        </a>
    </div>

    <div class="header">
        <img src="{{ asset('images/alumni-logo.JPG') }}" alt="FULAFIA Alumni Logo" class="header-logo">
        <h1>Federal University of Lafia</h1>
        <h2>Alumni Election Result Sheet</h2>
        <div class="header-divider"></div>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="stats">
        <p><strong>Total Accredited Voters:</strong> {{ number_format($election->getTotalAccreditedVoters()) }}</p>
        <p><strong>Total Votes Cast:</strong> {{ number_format($election->getTotalVotes()) }}</p>
        <p><strong>Voter Turnout:</strong> {{ number_format(($election->getTotalVotes() / max($election->getTotalAccreditedVoters(), 1)) * 100, 1) }}%</p>
    </div>

    @foreach($election->offices as $office)
        <div class="office-section">
            <h2 class="office-title">{{ $office->title }}</h2>
            <div class="candidate-list">
                @php
                    $totalVotes = $office->candidates->sum(function ($candidate) {
                        return $candidate->votes->count();
                    });
                    $candidates = $office->candidates->sortByDesc(function ($candidate) {
                        return $candidate->votes->count();
                    });
                @endphp

                @foreach($candidates as $candidate)
                    <div class="candidate-item">
                        <h4>{{ $candidate->alumni->user->name }}</h4>
                        <div class="candidate-stats">
                            <span>Votes Received: {{ number_format($candidate->votes->count()) }}</span>
                            <span>Percentage: {{ number_format(($candidate->votes->count() / max($totalVotes, 1)) * 100, 1) }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <p>ELCOM Chairman</p>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <p>ELCOM Secretary</p>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer">
        <p>This document was generated from Fulafia Alumni Election System 2025</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html> 