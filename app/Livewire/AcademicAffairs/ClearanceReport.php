<?php

namespace App\Livewire\AcademicAffairs;

use App\Exports\AcademicAffairsClearanceReportExport;
use App\Livewire\Concerns\HasClearanceReport;
use App\Models\OnboardingSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ClearanceReport extends Component
{
    use HasClearanceReport;

    protected function division(): string
    {
        return OnboardingSetting::DIVISION_ACADEMIC_AFFAIRS;
    }

    protected function clearedColumn(): string
    {
        return 'academic_affairs_cleared';
    }

    protected function layoutName(): string
    {
        return 'layouts.academic-affairs';
    }

    protected function pageTitle(): string
    {
        return 'Academic Affairs Clearance Report';
    }

    protected function canExportClearanceReport(): bool
    {
        return Auth::user()?->hasRole('academic-affairs') === true;
    }

    public function exportExcel()
    {
        $this->authorizeExport();
        $this->exportMessage = null;

        if (! $this->getQuery()->exists()) {
            $this->exportMessage = 'No records match the current filters. Adjust your filters and try again.';

            return null;
        }

        $generatedAt = now();
        $filename = 'academic-affairs-clearance-report-'.$generatedAt->format('Ymd-His').'.xlsx';

        return Excel::download(
            new AcademicAffairsClearanceReportExport(
                $this->getQuery(),
                $this->exportFilterCriteria(),
                $generatedAt
            ),
            $filename
        );
    }

    public function exportPdf()
    {
        $this->authorizeExport();
        $this->exportMessage = null;

        if (! $this->getQuery()->exists()) {
            $this->exportMessage = 'No records match the current filters. Adjust your filters and try again.';

            return null;
        }

        $generatedAt = now();
        $filename = 'academic-affairs-clearance-report-'.$generatedAt->format('Ymd-His').'.pdf';
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '256M');

        try {
            $html = view('exports.academic-affairs-clearance-report-pdf', [
                'rows' => $this->getQuery()->get(),
                'filters' => $this->exportFilterCriteria(),
                'generatedAt' => $generatedAt,
                'reason' => AcademicAffairsClearanceReportExport::REASON,
            ])->render();

            $pdf = app('dompdf.wrapper');
            $pdf->setOptions([
                'isPhpEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);
            $pdf->loadHTML($html);
            $pdf->setPaper('a4', 'landscape');
            $pdf->render();

            $canvas = $pdf->getDomPDF()->getCanvas();
            $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $canvas->page_text(720, 16, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.42, 0.45, 0.50]);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('Academic Affairs clearance report PDF generation failed', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            $this->exportMessage = 'Failed to generate the PDF. Please try again.';

            return null;
        } finally {
            ini_set('memory_limit', $originalMemoryLimit);
        }
    }

    protected function authorizeExport(): void
    {
        abort_unless($this->canExportClearanceReport(), 403);
    }
}
