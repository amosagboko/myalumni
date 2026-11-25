<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NyscOnboardingExport implements FromQuery, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
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
        return 'NYSC Onboarding';
    }

    public function headings(): array
    {
        return [
            'Name',
            'Matric',
            'Faculty',
            'Department',
            'Programme',
            'Year',
        ];
    }

    public function map($row): array
    {
        return [
            $row->user->name ?? '',
            $row->matric_number,
            $row->faculty,
            $row->department,
            $row->programme,
            $row->year_of_graduation !== null && $row->year_of_graduation !== ''
                ? (int) $row->year_of_graduation
                : null,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5:F5')->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('132977');
        $sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A1', 'NYSC Onboarding');
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A2', 'Generated: '.$this->generatedAt->format('M d, Y g:i A'));
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A3', 'Filters: '.$this->filtersLine());
                $sheet->mergeCells('A3:F3');
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
