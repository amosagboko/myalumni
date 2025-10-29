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
            // Increase memory limit for PDF generation
            $originalMemoryLimit = ini_get('memory_limit');
            ini_set('memory_limit', '256M');
            
            try {
                $html = view('pdf.alumni-report', $data)->render();

                $pdf = Pdf::loadHTML($html);
                $pdf->setPaper('a4', 'portrait');

                $fileName = 'alumni_report_' . str_replace(' ', '_', strtolower($user->name)) . '_' . now()->format('Ymd_His') . '.pdf';

                return $pdf->download($fileName);
            } finally {
                // Restore original memory limit
                ini_set('memory_limit', $originalMemoryLimit);
            }
        } catch (\Throwable $e) {
            Log::error('PDF Generation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'alumni_id' => $alumni->id ?? null,
            ]);
            
            // Show more detailed error in development, generic in production
            $errorMessage = config('app.debug') 
                ? 'Failed to generate PDF: ' . $e->getMessage() 
                : 'Failed to generate PDF. Please contact support if this issue persists.';
            
            return redirect()->route('reports')
                ->with('error', $errorMessage);
        }
    }
}

