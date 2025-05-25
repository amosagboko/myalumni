<?php

namespace App\Http\Controllers;

use App\Models\CategoryTransactionFee;
use App\Models\AlumniYear;
use App\Models\AlumniCategory;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeTemplateController extends Controller
{
    public function index()
    {
        $fees = CategoryTransactionFee::with(['category', 'alumniYear', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('fee-templates.index', compact('fees'));
    }

    public function show(CategoryTransactionFee $fee)
    {
        $fee->load(['category', 'alumniYear', 'feeType', 'transactions']);
        return view('fee-templates.show', compact('fee'));
    }

    public function create()
    {
        $alumniYears = AlumniYear::where('is_active', true)
            ->orderBy('year', 'desc')
            ->get();
        $categories = AlumniCategory::where('is_active', true)->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        
        return view('fee-templates.create', compact('alumniYears', 'categories', 'feeTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:alumni_categories,id',
            'alumni_year_id' => 'required|exists:alumni_years,id',
            'fee_type' => 'required|exists:fee_types,code',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Get the fee type ID from the code
            $feeType = FeeType::where('code', $validated['fee_type'])->first();
            if (!$feeType) {
                throw new \Exception('Fee type not found');
            }

            // Check if a fee already exists for this combination
            $existingFee = CategoryTransactionFee::where([
                'category_id' => $validated['category_id'],
                'fee_type_id' => $feeType->id,
                'alumni_year_id' => $validated['alumni_year_id']
            ])->first();

            if ($existingFee) {
                throw new \Exception(
                    'A fee already exists for this category, fee type, and alumni year combination. ' .
                    'Please edit the existing fee instead.'
                );
            }

            // Log the data we're trying to create
            Log::info('Attempting to create fee with data:', [
                'validated_data' => $validated,
                'fee_type_id' => $feeType->id,
                'fee_type_code' => $validated['fee_type']
            ]);

            // Create the fee with fee_type_id instead of fee_type
            $fee = CategoryTransactionFee::create([
                'category_id' => $validated['category_id'],
                'alumni_year_id' => $validated['alumni_year_id'],
                'fee_type_id' => $feeType->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_test_mode' => $validated['is_test_mode'] ?? false
            ]);

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated ?? null,
                'fee_type_code' => $validated['fee_type'] ?? null,
                'fee_type_id' => $feeType->id ?? null
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(CategoryTransactionFee $fee)
    {
        $fee->load('feeType');
        $alumniYears = AlumniYear::orderBy('year', 'desc')->get();
        $categories = AlumniCategory::where('is_active', true)->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        
        return view('fee-templates.edit', compact('fee', 'alumniYears', 'categories', 'feeTypes'));
    }

    public function update(Request $request, CategoryTransactionFee $fee)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:alumni_categories,id',
            'alumni_year_id' => 'required|exists:alumni_years,id',
            'fee_type' => 'required|exists:fee_types,code',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Get the fee type ID from the code
            $feeType = FeeType::where('code', $validated['fee_type'])->first();
            if (!$feeType) {
                throw new \Exception('Fee type not found');
            }

            // Update the fee with fee_type_id instead of fee_type
            $fee->update([
                'category_id' => $validated['category_id'],
                'alumni_year_id' => $validated['alumni_year_id'],
                'fee_type_id' => $feeType->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_test_mode' => $validated['is_test_mode'] ?? false
            ]);

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update fee. Please try again.');
        }
    }

    public function destroy(CategoryTransactionFee $fee)
    {
        try {
            DB::beginTransaction();

            // Check if there are any transactions
            if ($fee->transactions()->exists()) {
                return back()->with('error', 'Cannot delete fee with existing transactions.');
            }

            $fee->delete();

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete fee. Please try again.');
        }
    }

    public function activate(CategoryTransactionFee $fee)
    {
        try {
            DB::beginTransaction();

            $fee->update(['is_active' => true]);

            DB::commit();

            return back()->with('success', 'Fee activated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee activation failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to activate fee. Please try again.');
        }
    }

    public function deactivate(CategoryTransactionFee $fee)
    {
        try {
            DB::beginTransaction();

            $fee->update(['is_active' => false]);

            DB::commit();

            return back()->with('success', 'Fee deactivated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee deactivation failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to deactivate fee. Please try again.');
        }
    }
} 