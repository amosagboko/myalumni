<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\PaymentStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentStructureController extends Controller
{
    public function index()
    {
        $structures = PaymentStructure::query()
            ->with(['category', 'feeTemplates.feeType'])
            ->withCount(['feeTemplates', 'transactions'])
            ->orderByDesc('id')
            ->paginate(15);

        $stats = [
            'total' => PaymentStructure::count(),
            'combined' => PaymentStructure::where('payment_mode', PaymentStructure::MODE_COMBINED)->count(),
            'active' => PaymentStructure::where('is_active', true)->count(),
        ];

        return view('admin.payment-structures.index', compact('structures', 'stats'));
    }

    public function create()
    {
        return view('admin.payment-structures.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            $structure = PaymentStructure::create(collect($validated)->except('fee_template_ids')->all());
            $structure->feeTemplates()->sync($validated['fee_template_ids']);
        });

        return redirect()
            ->route('admin.payment-structures.index')
            ->with('success', 'Payment structure created.');
    }

    public function edit(PaymentStructure $paymentStructure)
    {
        $paymentStructure->load('feeTemplates');

        return view('admin.payment-structures.edit', array_merge(
            $this->formData(),
            ['paymentStructure' => $paymentStructure]
        ));
    }

    public function update(Request $request, PaymentStructure $paymentStructure)
    {
        $validated = $this->validatedData($request, $paymentStructure);

        DB::transaction(function () use ($validated, $paymentStructure) {
            $paymentStructure->update(collect($validated)->except('fee_template_ids')->all());
            $paymentStructure->feeTemplates()->sync($validated['fee_template_ids']);
        });

        return redirect()
            ->route('admin.payment-structures.index')
            ->with('success', 'Payment structure updated.');
    }

    public function destroy(PaymentStructure $paymentStructure)
    {
        if ($paymentStructure->transactions()->exists()) {
            return back()->with('error', 'Cannot delete a payment structure that already has transactions. Deactivate it instead.');
        }

        $paymentStructure->delete();

        return redirect()
            ->route('admin.payment-structures.index')
            ->with('success', 'Payment structure deleted.');
    }

    protected function formData(): array
    {
        $feeTemplates = FeeTemplate::with(['feeType', 'category'])
            ->orderByDesc('graduation_year')
            ->orderBy('fee_type_id')
            ->get()
            ->filter(fn (FeeTemplate $template) => ! $template->feeType?->isEoiFee())
            ->values();

        return [
            'feeTemplates' => $feeTemplates,
            'categories' => AlumniCategory::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function validatedData(Request $request, ?PaymentStructure $existing = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'display_title' => 'nullable|string|max:255',
            'payment_mode' => ['required', Rule::in([PaymentStructure::MODE_SEPARATE, PaymentStructure::MODE_COMBINED])],
            'graduation_year' => 'nullable|integer|min:0|max:'.(date('Y') + 10),
            'category_id' => 'nullable|exists:alumni_categories,id',
            'is_active' => 'sometimes|boolean',
            'fee_template_ids' => 'required|array|min:2',
            'fee_template_ids.*' => 'exists:fee_templates,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['display_title'] = $validated['display_title'] ?: $validated['name'];
        $validated['credo_service_code_key'] = 'combined';

        if ($validated['payment_mode'] === PaymentStructure::MODE_COMBINED && empty($validated['display_title'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'display_title' => 'A payer-facing title is required for combined payments.',
            ]);
        }

        $templates = FeeTemplate::with('feeType')->whereIn('id', $validated['fee_template_ids'])->get();
        if ($templates->contains(fn (FeeTemplate $template) => $template->feeType?->isEoiFee())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'fee_template_ids' => 'EOI screening fees cannot be included in a payment structure.',
            ]);
        }

        if ($validated['payment_mode'] === PaymentStructure::MODE_COMBINED) {
            $this->assertNoCombinedOverlap($validated['fee_template_ids'], $existing?->id);
        }

        return $validated;
    }

    protected function assertNoCombinedOverlap(array $feeTemplateIds, ?int $ignoreStructureId = null): void
    {
        $query = PaymentStructure::query()
            ->combined()
            ->active()
            ->whereHas('feeTemplates', fn ($q) => $q->whereIn('fee_templates.id', $feeTemplateIds));

        if ($ignoreStructureId) {
            $query->where('id', '!=', $ignoreStructureId);
        }

        $conflict = $query->with('feeTemplates')->first();
        if (! $conflict) {
            return;
        }

        $overlapping = $conflict->feeTemplates
            ->whereIn('id', $feeTemplateIds)
            ->pluck('description')
            ->filter()
            ->implode(', ');

        throw \Illuminate\Validation\ValidationException::withMessages([
            'fee_template_ids' => 'One or more selected fees already belong to the active combined structure “'.$conflict->name.'”'.($overlapping ? " ({$overlapping})" : '').'.',
        ]);
    }
}
