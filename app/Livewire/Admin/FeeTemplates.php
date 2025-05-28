<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\FeeTemplate;
use Livewire\WithPagination;

class FeeTemplates extends Component
{
    use WithPagination;

    public function render()
    {
        $fees = FeeTemplate::with(['category', 'feeType', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.fee-templates', [
            'fees' => $fees
        ]);
    }

    public function delete(FeeTemplate $fee)
    {
        if ($fee->transactions()->exists()) {
            session()->flash('error', 'Cannot delete fee template with existing transactions.');
            return;
        }

        $fee->delete();
        session()->flash('success', 'Fee template deleted successfully.');
    }

    public function toggleStatus(FeeTemplate $fee)
    {
        $fee->update(['is_active' => !$fee->is_active]);
        $status = $fee->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Fee template {$status} successfully.");
    }

    public function getFeeStructureLabel($fee)
    {
        return $fee->fee_structure === 'new' ? 'New Structure' : 'Old Structure';
    }

    public function getFeeTypeName($fee)
    {
        return $fee->feeType?->name ?? 'Unknown Fee Type';
    }

    public function getCategoryName($fee)
    {
        return $fee->category?->name ?? 'No Category';
    }

    public function getFormattedAmount($fee)
    {
        return '₦' . number_format($fee->amount, 2);
    }

    public function getValidityStatus($fee)
    {
        if (!$fee->is_active) {
            return 'Inactive';
        }

        if ($fee->valid_from > now()) {
            return 'Not Started';
        }

        if ($fee->valid_until && $fee->valid_until < now()) {
            return 'Expired';
        }

        return 'Active';
    }
} 