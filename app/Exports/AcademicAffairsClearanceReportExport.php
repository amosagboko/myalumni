<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AcademicAffairsClearanceReportExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public const REASON = 'To collect result statement';

    /**
     * @param  array<string, string>  $filters
     */
    public function __construct(
        protected Builder $query,
        protected array $filters,
        protected Carbon $generatedAt,
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function title(): string
    {
        return 'Clearance Report';
    }

    public function headings(): array
    {
        return [
            'When',
            'Alumni',
            'Matric',
            'Status',
            'Actor',
            'Reason',
            'Year',
        ];
    }

    public function map($row): array
    {
        $clearedAt = $row->cleared_at
            ? ExcelDate::dateTimeToExcel(Carbon::parse($row->cleared_at))
            : null;

        return [
            $clearedAt,
            $row->alumni_name,
            $row->matric_number,
            $row->is_cleared ? 'Cleared' : 'Not cleared',
            $row->actor_name ?: '—',
            self::REASON,
            $row->year_of_graduation !== null && $row->year_of_graduation !== ''
                ? (int) $row->year_of_graduation
                : null,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DATETIME,
            'G' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5:G5')->getFont()->setBold(true)->setColor(
            new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF')
        );
        $sheet->getStyle('A5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('132977');
        $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A1', 'Academic Affairs Clearance Report');
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A2', 'Generated: '.$this->generatedAt->format('M d, Y g:i A'));
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A3', 'Filters: '.$this->filtersLine());
                $sheet->mergeCells('A3:G3');
            },
        ];
    }

    protected function filtersLine(): string
    {
        return collect($this->filters)
            ->map(fn (string $value, string $label) => $label.': '.$value)
            ->implode('  |  ');
    }
}
