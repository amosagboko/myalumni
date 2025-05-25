<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Election Certificates - {{ $election->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        function printDocument() {
            window.print();
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .certificate-container {
                page-break-after: always;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .certificate {
                border: none;
                box-shadow: none;
            }
        }

        .certificate-container {
            max-width: 800px;
            margin: 0 auto 40px;
            padding: 20px;
        }

        .certificate {
            background: white;
            border: 2px solid #c9a959;
            border-radius: 8px;
            padding: 40px;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .certificate::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 1px solid #c9a959;
            border-radius: 4px;
            pointer-events: none;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .certificate-header img {
            width: 120px;
            height: auto;
            margin-bottom: 20px;
        }

        .certificate-title {
            font-family: 'Playfair Display', serif;
            color: #2c3e50;
            font-size: 36px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .certificate-subtitle {
            color: #7f8c8d;
            font-size: 18px;
            margin: 10px 0;
            font-weight: 300;
        }

        .certificate-content {
            text-align: center;
            margin: 40px 0;
            font-size: 16px;
            line-height: 1.8;
            color: #34495e;
        }

        .winner-name {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .office-title {
            font-size: 24px;
            color: #c9a959;
            margin: 15px 0;
            font-weight: 500;
        }

        .certificate-footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #2c3e50;
            margin-top: 50px;
            padding-top: 10px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .certificate-date {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 14px;
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
            background-color: #c9a959;
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
            background-color: #b38f4a;
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

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(201, 169, 89, 0.05);
            white-space: nowrap;
            pointer-events: none;
            z-index: 1;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .certificate-border {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 1px solid rgba(201, 169, 89, 0.3);
            border-radius: 6px;
            pointer-events: none;
        }

        .certificate-details {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }

        .certificate-details p {
            margin: 5px 0;
        }

        .certificate-verification {
            position: absolute;
            bottom: 20px;
            right: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .qr-code {
            width: 100px;
            height: 100px;
            margin: 0 auto 5px;
            padding: 5px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .verification-text {
            font-size: 10px;
            color: #666;
            max-width: 120px;
            margin: 0 auto;
        }

        .certificate-number {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        @media print {
            .certificate-verification {
                position: absolute;
                bottom: 20px;
                right: 20px;
            }
            .qr-code {
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="print-container no-print">
        <a href="javascript:void(0)" onclick="printDocument()" class="print-button">
            <i class="fas fa-print"></i>Print Certificates
        </a>
    </div>

    @foreach($election->offices as $office)
        @php
            $winner = $office->candidates->sortByDesc(function ($candidate) {
                return $candidate->votes->count();
            })->first();
            
            if ($winner) {
                $totalVotes = $office->candidates->sum(function ($candidate) {
                    return $candidate->votes->count();
                });
                $winnerVotes = $winner->votes->count();
                $percentage = $totalVotes > 0 ? ($winnerVotes / $totalVotes) * 100 : 0;
                
                // Generate a unique certificate number
                $certificateNumber = strtoupper(substr(md5($election->id . $office->id . $winner->id), 0, 8));
                
                // Create verification URL
                $verificationUrl = route('elcom.verify.certificate', [
                    'election' => $election->id,
                    'office' => $office->id,
                    'winner' => $winner->id,
                    'code' => $certificateNumber
                ]);
            }
        @endphp
        
        @if($winner)
            <div class="certificate-container">
                <div class="certificate">
                    <div class="watermark">CERTIFICATE OF ELECTION</div>
                    <div class="certificate-border"></div>
                    
                    <div class="certificate-number">
                        Certificate #: {{ $certificateNumber }}
                    </div>
                    
                    <div class="certificate-header">
                        <img src="{{ asset('images/alumni-logo.JPG') }}" alt="FULAFIA Alumni Logo">
                        <h1 class="certificate-title">Certificate of Election</h1>
                        <div class="certificate-subtitle">Federal University of Lafia Alumni Association</div>
                    </div>

                    <div class="certificate-content">
                        <p>This is to certify that</p>
                        <div class="winner-name">{{ $winner->alumni->user->name }}</div>
                        <p>has been duly elected to the position of</p>
                        <div class="office-title">{{ $office->title }}</div>
                        <p>in the {{ $election->title }} held on {{ $election->voting_end->format('F j, Y') }}.</p>
                        
                        <div class="certificate-details">
                            <p><strong>Total Votes Received:</strong> {{ number_format($winnerVotes) }}</p>
                            <p><strong>Percentage of Votes:</strong> {{ number_format($percentage, 1) }}%</p>
                            <p><strong>Term Duration:</strong> {{ $office->term_duration }} years</p>
                        </div>
                    </div>

                    <div class="certificate-footer">
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
                    </div>

                    <div class="certificate-date">
                        <p>Issued on: {{ now()->format('F j, Y') }}</p>
                    </div>

                    <div class="certificate-verification">
                        {!! QrCode::size(100)
                            ->format('svg')
                            ->errorCorrection('H')
                            ->generate($verificationUrl) !!}
                        <div class="verification-text">
                            Scan to verify certificate authenticity
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</body>
</html> 