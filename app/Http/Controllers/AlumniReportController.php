<?php

namespace App\Http\Controllers;

use App\Services\Alumni\ClearanceFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AlumniReportController extends Controller
{
    public function __construct(
        private readonly ClearanceFormService $clearanceFormService
    ) {}

    public function print(): View|RedirectResponse
    {
        $user = Auth::user();
        $alumni = $user?->alumni;

        if (! $alumni) {
            return redirect()->route('reports')
                ->with('error', 'Alumni information not found.');
        }

        if (! $this->clearanceFormService->canAccess($alumni)) {
            return redirect()->route('reports')
                ->with('error', 'Please complete your profile and payments before printing the clearance form.');
        }

        return view('alumni.clearance.print', $this->clearanceFormService->context($user, $alumni));
    }

    public function downloadPdf(): Response|RedirectResponse
    {
        $user = Auth::user();
        $alumni = $user?->alumni;

        if (! $alumni) {
            return redirect()->route('reports')
                ->with('error', 'Alumni information not found.');
        }

        if (! $this->clearanceFormService->canAccess($alumni)) {
            return redirect()->route('reports')
                ->with('error', 'Please complete your profile and payments before downloading the clearance form.');
        }

        $data = $this->clearanceFormService->context($user, $alumni);

        try {
            $originalMemoryLimit = ini_get('memory_limit');
            ini_set('memory_limit', '256M');

            try {
                $html = view('alumni.clearance.pdf', $data)->render();
                $dompdf = app('dompdf.wrapper');
                $pdf = $dompdf->loadHTML($html);
                $pdf->setPaper('a4', 'portrait');

                return $pdf->download($this->clearanceFormService->pdfFileName($user));
            } finally {
                ini_set('memory_limit', $originalMemoryLimit);
            }
        } catch (\Throwable $e) {
            Log::error('Clearance form PDF generation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id ?? null,
                'alumni_id' => $alumni->id ?? null,
            ]);

            return redirect()->route('reports')
                ->with('error', 'Failed to generate PDF. Please try again or use Print Form instead.');
        }
    }
}
