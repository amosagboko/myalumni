<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\CategoryTransactionFee;
use Livewire\WithPagination;

class FeeTemplates extends Component
{
    use WithPagination;

    public function render()
    {
        $fees = CategoryTransactionFee::with(['category', 'alumniYear', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.fee-templates', [
            'fees' => $fees
        ]);
    }

    public function delete(CategoryTransactionFee $fee)
    {
        if ($fee->transactions()->exists()) {
            session()->flash('error', 'Cannot delete fee with existing transactions.');
            return;
        }

        $fee->delete();
        session()->flash('success', 'Fee deleted successfully.');
    }

    public function toggleStatus(CategoryTransactionFee $fee)
    {
        $fee->update(['is_active' => !$fee->is_active]);
        $status = $fee->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Fee {$status} successfully.");
    }
} 