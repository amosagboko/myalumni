<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                
                // Try resolving from container (more reliable than facade)
                $dompdf = app('dompdf.wrapper');
                $pdf = $dompdf->loadHTML($html);
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
            
            // Always show the actual error message for debugging
            $errorMessage = 'Failed to generate PDF: ' . $e->getMessage() . 
                ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
            
            return redirect()->route('reports')
                ->with('error', $errorMessage);
        }
    }
}

