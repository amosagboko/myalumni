<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Services\Alumni\SkippedPaymentReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SkippedPaymentController extends Controller
{
    public function __construct(
        protected SkippedPaymentReportService $report
    ) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->report->rows($filters);

        $perPage = 50;
        $page = max(1, (int) $request->input('page', 1));
        $alumni = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.skipped-payments.index', [
            'alumni' => $alumni,
            'stats' => [
                'total' => $rows->count(),
                'bypassed' => $rows->where('bypassed_payment_gate', true)->count(),
                'online' => $rows->where('source', 'Online')->count(),
            ],
            'years' => Alumni::query()
                ->where('year_of_graduation', '>=', SkippedPaymentReportService::DEFAULT_MIN_YEAR)
                ->distinct()
                ->orderByDesc('year_of_graduation')
                ->pluck('year_of_graduation'),
            'categories' => AlumniCategory::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->report->rows($filters);
        $filename = 'skipped-payments-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->report->csvHeaders());

            foreach ($rows as $row) {
                fputcsv($handle, $this->report->csvRow($row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array{year:int|null, source:string, search:string, category_id:int|null, bypassed:bool, from:?string, to:?string}
     */
    private function filters(Request $request): array
    {
        return [
            'year' => $request->filled('year') ? (int) $request->input('year') : null,
            'source' => in_array($request->input('source'), ['all', 'online', 'uploaded'], true)
                ? $request->input('source')
                : 'all',
            'search' => (string) $request->input('search', ''),
            'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
            'bypassed' => $request->boolean('bypassed'),
            'from' => $request->filled('from') ? (string) $request->input('from') : null,
            'to' => $request->filled('to') ? (string) $request->input('to') : null,
        ];
    }
}
