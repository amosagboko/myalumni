<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniYear;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\Transaction;
use App\Services\AlumniDuesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentYearController extends Controller
{
    public function __construct(protected AlumniDuesService $duesService)
    {
    }

    public function index()
    {
        $years = AlumniYear::orderByDesc('year')->paginate(15);

        $annualDueTypeId = FeeType::where('code', FeeType::ANNUAL_DUE_CODE)->value('id');

        $years->getCollection()->transform(function (AlumniYear $year) use ($annualDueTypeId) {
            $year->annual_due_template = $year->annualDueTemplate();
            $year->onboarding_template_count = $year->onboardingTemplates()->count();
            $year->annual_paid_count = $year->annual_due_template
                ? Transaction::where('fee_template_id', $year->annual_due_template->id)->where('status', 'paid')->count()
                : 0;

            return $year;
        });

        $activeYear = AlumniYear::where('is_active', true)->first();

        return view('admin.payment-years.index', compact('years', 'activeYear', 'annualDueTypeId'));
    }

    public function create()
    {
        $previousYear = AlumniYear::orderByDesc('year')->first();
        $suggestedYear = $previousYear ? $previousYear->year + 1 : (int) date('Y');

        return view('admin.payment-years.create', compact('suggestedYear', 'previousYear'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100|unique:alumni_years,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'sometimes|boolean',
            'copy_annual_due_from_previous' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return DB::transaction(function () use ($validated, $request) {
            if ($validated['is_active']) {
                AlumniYear::where('is_active', true)->update(['is_active' => false]);
            }

            $year = AlumniYear::create([
                'year' => $validated['year'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $validated['is_active'],
            ]);

            if ($request->boolean('copy_annual_due_from_previous')) {
                $this->copyAnnualDueFromPrevious($year);
            }

            if ($validated['is_active'] && $year->fresh()->annualDueTemplate()) {
                $assigned = $this->duesService->assignAnnualDuesForPaymentYear($year->fresh());
                $flash = "Payment year {$year->year} created and activated.";
                if ($assigned > 0) {
                    $flash .= " Assigned {$assigned} pending annual due(s).";
                }

                return redirect()
                    ->route('admin.payment-years.show', $year)
                    ->with('success', $flash);
            }

            return redirect()
                ->route('admin.payment-years.show', $year)
                ->with('success', "Payment year {$year->year} created. Configure the annual due and onboarding fees.");
        });
    }

    public function show(AlumniYear $paymentYear)
    {
        $yearSpecificAnnualDue = $paymentYear->yearSpecificAnnualDueTemplate();
        $resolvedAnnualDue = $paymentYear->annualDueTemplate();
        $sharedAnnualDue = $yearSpecificAnnualDue ? null : $paymentYear->sharedAnnualDueTemplate();

        $onboardingByCohort = $paymentYear->onboardingTemplatesByCohort();
        $onboardingFeeTypes = FeeType::onboardingTypes()->get();
        $previousYear = AlumniYear::where('year', '<', $paymentYear->year)->orderByDesc('year')->first();
        $previousAnnualDue = $previousYear?->yearSpecificAnnualDueTemplate()
            ?? $previousYear?->sharedAnnualDueTemplate();

        $annualStats = [
            'paid' => 0,
            'pending' => 0,
        ];

        $statsTemplate = $resolvedAnnualDue;
        if ($statsTemplate) {
            $annualStats['paid'] = Transaction::where('fee_template_id', $statsTemplate->id)
                ->where('status', 'paid')->count();
            $annualStats['pending'] = Transaction::where('fee_template_id', $statsTemplate->id)
                ->where('status', 'pending')->count();
        }

        return view('admin.payment-years.show', compact(
            'paymentYear',
            'yearSpecificAnnualDue',
            'resolvedAnnualDue',
            'sharedAnnualDue',
            'onboardingByCohort',
            'onboardingFeeTypes',
            'previousYear',
            'previousAnnualDue',
            'annualStats',
        ));
    }

    public function storeAnnualDue(Request $request, AlumniYear $paymentYear)
    {
        if ($paymentYear->yearSpecificAnnualDueTemplate()) {
            return back()->with('error', 'An annual due already exists for this payment year. Use Update annual due below.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
        ]);

        $feeType = FeeType::where('code', FeeType::ANNUAL_DUE_CODE)->firstOrFail();

        FeeTemplate::create([
            'fee_type_id' => $feeType->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'category_id' => null,
            'graduation_year' => $paymentYear->year,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? "Annual alumni due for {$paymentYear->year}",
            'is_active' => $request->boolean('is_active', true),
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'] ?? null,
        ]);

        $message = "Annual due for {$paymentYear->year} has been configured.";
        if ($paymentYear->is_active) {
            $assigned = $this->duesService->assignAnnualDuesForPaymentYear($paymentYear->fresh());
            if ($assigned > 0) {
                $message .= " Assigned {$assigned} pending annual due(s).";
            }
        }

        return back()->with('success', $message);
    }

    public function updateAnnualDue(Request $request, AlumniYear $paymentYear, FeeTemplate $feeTemplate)
    {
        $this->assertAnnualDueTemplate($paymentYear, $feeTemplate);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
        ]);

        $feeTemplate->update([
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? $feeTemplate->description,
            'is_active' => $request->boolean('is_active', true),
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'] ?? null,
        ]);

        return back()->with('success', 'Annual due updated successfully.');
    }

    public function activate(AlumniYear $paymentYear)
    {
        AlumniYear::where('is_active', true)
            ->where('id', '!=', $paymentYear->id)
            ->update(['is_active' => false]);

        $paymentYear->update(['is_active' => true]);

        $message = "Payment year {$paymentYear->year} is now the active year.";
        if ($paymentYear->annualDueTemplate()) {
            $assigned = $this->duesService->assignAnnualDuesForPaymentYear($paymentYear->fresh());
            if ($assigned > 0) {
                $message .= " Assigned {$assigned} pending annual due(s).";
            }
        }

        return back()->with('success', $message);
    }

    public function copyAnnualDue(AlumniYear $paymentYear)
    {
        try {
            $this->copyAnnualDueFromPrevious($paymentYear);

            return back()->with('success', "Annual due copied from the previous payment year.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function copyAnnualDueFromPrevious(AlumniYear $paymentYear): void
    {
        if ($paymentYear->yearSpecificAnnualDueTemplate()) {
            throw new \RuntimeException('This payment year already has an annual due configured.');
        }

        $previousYear = AlumniYear::where('year', '<', $paymentYear->year)->orderByDesc('year')->first();
        if (!$previousYear) {
            throw new \RuntimeException('No previous payment year found to copy from.');
        }

        $source = $previousYear->yearSpecificAnnualDueTemplate()
            ?? $previousYear->sharedAnnualDueTemplate();
        if (!$source) {
            throw new \RuntimeException("Previous year ({$previousYear->year}) has no annual due configured.");
        }

        $feeType = FeeType::where('code', FeeType::ANNUAL_DUE_CODE)->firstOrFail();

        FeeTemplate::create([
            'fee_type_id' => $feeType->id,
            'fee_purpose' => FeeTemplate::PURPOSE_ANNUAL_RENEWAL,
            'category_id' => null,
            'graduation_year' => $paymentYear->year,
            'amount' => $source->amount,
            'description' => "Annual alumni due for {$paymentYear->year}",
            'is_active' => true,
            'valid_from' => $paymentYear->start_date,
            'valid_until' => $paymentYear->end_date,
        ]);
    }

    protected function assertAnnualDueTemplate(AlumniYear $paymentYear, FeeTemplate $feeTemplate): void
    {
        if ((int) $feeTemplate->graduation_year !== (int) $paymentYear->year
            || $feeTemplate->fee_purpose !== FeeTemplate::PURPOSE_ANNUAL_RENEWAL) {
            abort(404);
        }
    }
}
