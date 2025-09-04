<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\AlumniCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeTemplate::with(['feeType', 'category', 'transactions']);

        // Apply filters
        if ($request->filled('fee_type')) {
            $query->where('fee_type_id', $request->fee_type);
        }

        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $feeTemplates = $query->orderBy('created_at', 'desc')->paginate(15);
        $feeTypes = FeeType::where('is_active', true)->get();
        $categories = AlumniCategory::where('is_active', true)->get();

        return view('admin.fee-templates.index', compact('feeTemplates', 'feeTypes', 'categories'));
    }

    public function create()
    {
        $feeTypes = FeeType::where('is_active', true)->get();
        $categories = AlumniCategory::where('is_active', true)->get();

        return view('admin.fee-templates.create', compact('feeTypes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'graduation_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category_id' => 'nullable|exists:alumni_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from'
        ]);

        // Validate category requirement for 2025+
        if ($validated['graduation_year'] >= 2025 && empty($validated['category_id'])) {
            return back()->withInput()->withErrors(['category_id' => 'Category is required for 2025+ graduates']);
        }

        try {
            DB::beginTransaction();

            // Check for existing fee template
            $existingQuery = FeeTemplate::where([
                'fee_type_id' => $validated['fee_type_id'],
                'graduation_year' => $validated['graduation_year'],
                'valid_from' => $validated['valid_from']
            ]);

            if ($validated['graduation_year'] >= 2025) {
                $existingQuery->where('category_id', $validated['category_id']);
            } else {
                $existingQuery->whereNull('category_id');
            }

            $existingFee = $existingQuery->first();

            if ($existingFee) {
                throw new \Exception('A fee template already exists for this fee type, graduation year, and valid from date.');
            }

            $feeTemplate = FeeTemplate::create([
                'fee_type_id' => $validated['fee_type_id'],
                'category_id' => $validated['category_id'],
                'graduation_year' => $validated['graduation_year'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until']
            ]);

            DB::commit();

            return redirect()
                ->route('admin.fee-templates.index')
                ->with('success', 'Fee template created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(FeeTemplate $feeTemplate)
    {
        $feeTypes = FeeType::where('is_active', true)->get();
        $categories = AlumniCategory::where('is_active', true)->get();

        return view('admin.fee-templates.edit', compact('feeTemplate', 'feeTypes', 'categories'));
    }

    public function update(Request $request, FeeTemplate $feeTemplate)
    {
        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'graduation_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category_id' => 'nullable|exists:alumni_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from'
        ]);

        // Validate category requirement for 2025+
        if ($validated['graduation_year'] >= 2025 && empty($validated['category_id'])) {
            return back()->withInput()->withErrors(['category_id' => 'Category is required for 2025+ graduates']);
        }

        try {
            DB::beginTransaction();

            // Check for existing fee template (excluding current one)
            $existingQuery = FeeTemplate::where([
                'fee_type_id' => $validated['fee_type_id'],
                'graduation_year' => $validated['graduation_year'],
                'valid_from' => $validated['valid_from']
            ])->where('id', '!=', $feeTemplate->id);

            if ($validated['graduation_year'] >= 2025) {
                $existingQuery->where('category_id', $validated['category_id']);
            } else {
                $existingQuery->whereNull('category_id');
            }

            $existingFee = $existingQuery->first();

            if ($existingFee) {
                throw new \Exception('Another fee template already exists for this fee type, graduation year, and valid from date.');
            }

            $feeTemplate->update([
                'fee_type_id' => $validated['fee_type_id'],
                'category_id' => $validated['category_id'],
                'graduation_year' => $validated['graduation_year'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until']
            ]);

            DB::commit();

            return redirect()
                ->route('admin.fee-templates.index')
                ->with('success', 'Fee template updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template update failed', [
                'error' => $e->getMessage(),
                'fee_template_id' => $feeTemplate->id,
                'data' => $validated
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(FeeTemplate $feeTemplate)
    {
        try {
            DB::beginTransaction();

            // Check if there are any transactions
            if ($feeTemplate->transactions()->exists()) {
                return back()->with('error', 'Cannot delete fee template with existing transactions.');
            }

            $feeTemplate->delete();

            DB::commit();

            return redirect()
                ->route('admin.fee-templates.index')
                ->with('success', 'Fee template deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template deletion failed', [
                'error' => $e->getMessage(),
                'fee_template_id' => $feeTemplate->id
            ]);

            return back()->with('error', 'Failed to delete fee template. Please try again.');
        }
    }

    public function activate(FeeTemplate $feeTemplate)
    {
        try {
            $feeTemplate->update(['is_active' => true]);

            return redirect()
                ->route('admin.fee-templates.index')
                ->with('success', 'Fee template activated successfully.');

        } catch (\Exception $e) {
            Log::error('Fee template activation failed', [
                'error' => $e->getMessage(),
                'fee_template_id' => $feeTemplate->id
            ]);

            return back()->with('error', 'Failed to activate fee template. Please try again.');
        }
    }

    public function deactivate(FeeTemplate $feeTemplate)
    {
        try {
            $feeTemplate->update(['is_active' => false]);

            return redirect()
                ->route('admin.fee-templates.index')
                ->with('success', 'Fee template deactivated successfully.');

        } catch (\Exception $e) {
            Log::error('Fee template deactivation failed', [
                'error' => $e->getMessage(),
                'fee_template_id' => $feeTemplate->id
            ]);

            return back()->with('error', 'Failed to deactivate fee template. Please try again.');
        }
    }
} 