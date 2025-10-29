<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AlumniReportController extends Controller
{
    public function downloadPdf()
    {
        $user = Auth::user();
        $alumni = $user->alumni;

        if (!$alumni) {
            return redirect()->route('reports')
                ->with('error', 'Alumni information not found.');
        }

        $data = [
            'user' => $user,
            'alumni' => $alumni,
            'generatedAt' => now(),
        ];

        try {
            $html = view('pdf.alumni-report', $data)->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

            $fileName = 'alumni_report_' . str_replace(' ', '_', strtolower($user->name)) . '_' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return redirect()->route('reports')
                ->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}

