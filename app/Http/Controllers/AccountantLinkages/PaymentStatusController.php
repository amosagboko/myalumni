<?php

namespace App\Http\Controllers\AccountantLinkages;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Services\Alumni\AccountantLinkagePaymentService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentStatusController extends Controller
{
    public function __construct(
        private readonly AccountantLinkagePaymentService $payments
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeOfficer();

        $status = strtolower((string) $request->input('status', ''));
        $filters = [
            'year' => $request->filled('year') ? (int) $request->input('year') : null,
            'search' => (string) $request->input('search', ''),
            'status' => in_array($status, ['paid', 'pending'], true) ? $status : '',
        ];

        if ($filters['year'] !== null && $filters['year'] < AccountantLinkagePaymentService::MIN_YEAR) {
            $filters['year'] = null;
        }

        $perPage = 25;

        if ($filters['status'] === '') {
            $alumni = $this->payments->query($filters)->paginate($perPage)->withQueryString();
            $alumni->setCollection($alumni->getCollection()->map(fn (Alumni $alumnus) => $this->row($alumnus)));
        } else {
            $rows = $this->payments->query($filters)
                ->get()
                ->map(fn (Alumni $alumnus) => $this->row($alumnus))
                ->filter(fn (array $row) => strcasecmp($row['payments'], $filters['status']) === 0)
                ->values();
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
        }

        return view('accountant-linkages.payments.index', [
            'alumni' => $alumni,
            'years' => $this->payments->years(),
            'filters' => $filters,
            'minYear' => AccountantLinkagePaymentService::MIN_YEAR,
        ]);
    }

    public function show(Alumni $alumni): View
    {
        $this->authorizeOfficer();
        $alumni = $this->payments->findEligible($alumni->id);

        return view('accountant-linkages.payments.show', $this->detail($alumni));
    }

    public function print(Alumni $alumni): View
    {
        $this->authorizeOfficer();
        $alumni = $this->payments->findEligible($alumni->id);

        return view('accountant-linkages.payments.print', $this->detail($alumni));
    }

    /**
     * @return array{id: int, name: string, matric_number: string, faculty: string, department: string, year: int|string, payments: string}
     */
    private function row(Alumni $alumnus): array
    {
        return [
            'id' => $alumnus->id,
            'name' => $alumnus->user?->name ?: '—',
            'matric_number' => $alumnus->matric_number ?: '—',
            'faculty' => $alumnus->faculty ?: '—',
            'department' => $alumnus->department ?: '—',
            'year' => $alumnus->year_of_graduation ?: '—',
            'payments' => $this->payments->statusLabel($alumnus),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Alumni $alumni): array
    {
        return [
            'alumni' => $alumni,
            'photoUrl' => $this->payments->photoUrl($alumni),
            'rows' => $this->payments->ledger($alumni),
            'status' => $this->payments->statusLabel($alumni),
        ];
    }

    private function authorizeOfficer(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('accountant-linkages')) {
            abort(403);
        }
    }
}
