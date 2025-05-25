<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeeTypeController extends Controller
{
    public function index()
    {
        $feeTypes = FeeType::orderBy('name')
            ->paginate(10);

        return view('admin.fee-types.index', compact('feeTypes'));
    }

    public function create()
    {
        return view('admin.fee-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fee_types,name',
            'code' => 'nullable|string|max:255|unique:fee_types,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $feeType = FeeType::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.fee-types.index')
                ->with('success', 'Fee type created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee type creation failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create fee type. Please try again.');
        }
    }

    public function edit(FeeType $feeType)
    {
        if ($feeType->is_system) {
            return back()->with('error', 'System fee types cannot be edited.');
        }

        return view('admin.fee-types.edit', compact('feeType'));
    }

    public function update(Request $request, FeeType $feeType)
    {
        if ($feeType->is_system) {
            return back()->with('error', 'System fee types cannot be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fee_types,name,' . $feeType->id,
            'code' => 'nullable|string|max:255|unique:fee_types,code,' . $feeType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $feeType->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.fee-types.index')
                ->with('success', 'Fee type updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee type update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update fee type. Please try again.');
        }
    }

    public function destroy(FeeType $feeType)
    {
        if ($feeType->is_system) {
            return back()->with('error', 'System fee types cannot be deleted.');
        }

        if ($feeType->transactionFees()->exists()) {
            return back()->with('error', 'Cannot delete fee type that has associated fees.');
        }

        try {
            DB::beginTransaction();

            $feeType->delete();

            DB::commit();

            return redirect()
                ->route('admin.fee-types.index')
                ->with('success', 'Fee type deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee type deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete fee type. Please try again.');
        }
    }

    public function toggleStatus(FeeType $feeType)
    {
        if ($feeType->is_system) {
            return back()->with('error', 'System fee types cannot be deactivated.');
        }

        try {
            DB::beginTransaction();

            $feeType->update(['is_active' => !$feeType->is_active]);

            DB::commit();

            $status = $feeType->is_active ? 'activated' : 'deactivated';
            return back()->with('success', "Fee type {$status} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee type status toggle failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to update fee type status. Please try again.');
        }
    }
} 