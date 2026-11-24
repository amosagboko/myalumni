<?php

namespace App\Http\Controllers\AccountantLinkages;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Services\Alumni\AccountantLinkagePaymentService;
use Illuminate\Http\Request;
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

        $filters = [
            'year' => $request->filled('year') ? (int) $request->input('year') : null,
            'search' => (string) $request->input('search', ''),
        ];

        if ($filters['year'] !== null && $filters['year'] < AccountantLinkagePaymentService::MIN_YEAR) {
            $filters['year'] = null;
        }

        $alumni = $this->payments->query($filters)->paginate(25)->withQueryString();
        $alumni->setCollection($alumni->getCollection()->map(function (Alumni $alumnus) {
            return [
                'id' => $alumnus->id,
                'name' => $alumnus->user?->name ?: '—',
                'matric_number' => $alumnus->matric_number ?: '—',
                'faculty' => $alumnus->faculty ?: '—',
                'department' => $alumnus->department ?: '—',
                'year' => $alumnus->year_of_graduation ?: '—',
                'payments' => $this->payments->statusLabel($alumnus),
            ];
        }));

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
