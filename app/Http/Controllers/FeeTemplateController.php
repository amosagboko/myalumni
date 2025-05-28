<?php

namespace App\Http\Controllers;

use App\Models\FeeTemplate;
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
        $fees = FeeTemplate::with(['feeType', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('fee-templates.index', compact('fees'));
    }

    public function show(FeeTemplate $fee)
    {
        $fee->load(['feeType', 'transactions']);
        return view('fee-templates.show', compact('fee'));
    }

    public function create()
    {
        $alumniYears = AlumniYear::where('is_active', true)
            ->orderBy('year', 'desc')
            ->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        return view('fee-templates.create', compact('alumniYears', 'feeTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'graduation_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount' => 'required|numeric|min:0',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from'
        ]);
        try {
            DB::beginTransaction();
            $existingFee = FeeTemplate::where([
                'fee_type_id' => $validated['fee_type_id'],
                'graduation_year' => $validated['graduation_year'],
                'valid_from' => $validated['valid_from']
            ])->first();
            if ($existingFee) {
                throw new \Exception('A fee already exists for this fee type, graduation year, and valid from. Please edit the existing fee instead.');
            }
            $fee = FeeTemplate::create([
                'graduation_year' => $validated['graduation_year'],
                'fee_type_id' => $validated['fee_type_id'],
                'amount' => $validated['amount'],
                'name' => $validated['name'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'] ?? null
            ]);
            DB::commit();
            return redirect()->route('fee-templates.index')->with('success', 'Fee template created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated ?? null
            ]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(FeeTemplate $fee)
    {
        $fee->load('feeType');
        $alumniYears = AlumniYear::orderBy('year', 'desc')->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        return view('fee-templates.edit', compact('fee', 'alumniYears', 'feeTypes'));
    }

    public function update(Request $request, FeeTemplate $fee)
    {
        $validated = $request->validate([
            'graduation_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount' => 'required|numeric|min:0',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from'
        ]);
        try {
            DB::beginTransaction();
            $existingFee = FeeTemplate::where([
                'fee_type_id' => $validated['fee_type_id'],
                'graduation_year' => $validated['graduation_year'],
                'valid_from' => $validated['valid_from']
            ])->where('id', '!=', $fee->id)->first();
            if ($existingFee) {
                throw new \Exception('Another fee already exists for this fee type, graduation year, and valid from.');
            }
            $fee->update([
                'graduation_year' => $validated['graduation_year'],
                'fee_type_id' => $validated['fee_type_id'],
                'amount' => $validated['amount'],
                'name' => $validated['name'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'] ?? null
            ]);
            DB::commit();
            return redirect()->route('fee-templates.index')->with('success', 'Fee template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validated ?? null
            ]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(FeeTemplate $fee)
    {
        try {
            DB::beginTransaction();

            // Check if there are any transactions
            if ($fee->transactions()->exists()) {
                return back()->with('error', 'Cannot delete fee template with existing transactions.');
            }

            $fee->delete();

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee template deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to delete fee template. Please try again.');
        }
    }

    public function activate(FeeTemplate $fee)
    {
        try {
            DB::beginTransaction();

            $fee->update(['is_active' => true]);

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee template activated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template activation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to activate fee template. Please try again.');
        }
    }

    public function deactivate(FeeTemplate $fee)
    {
        try {
            DB::beginTransaction();

            $fee->update(['is_active' => false]);

            DB::commit();

            return redirect()
                ->route('fee-templates.index')
                ->with('success', 'Fee template deactivated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee template deactivation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to deactivate fee template. Please try again.');
        }
    }
} 