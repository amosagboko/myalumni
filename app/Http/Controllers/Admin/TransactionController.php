<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['alumni.user', 'feeTemplate.feeType', 'feeTemplate.category']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('alumni.user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('alumni', function ($alumniQuery) use ($search) {
                    $alumniQuery->where('matric_number', 'like', "%{$search}%");
                })
                ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply fee type filter
        if ($request->filled('fee_type')) {
            $query->whereHas('feeTemplate', function ($q) use ($request) {
                $q->where('fee_type_id', $request->fee_type);
            });
        }

        // Apply date range filter
        if ($request->filled('date_range')) {
            $dateRange = $request->date_range;
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Transaction::count(),
            'paid' => Transaction::where('status', 'paid')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
        ];

        // Get filter options
        $feeTypes = FeeType::where('is_active', true)->get();

        return view('admin.transactions.index', compact('transactions', 'stats', 'feeTypes'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['alumni.user', 'feeTemplate.feeType', 'feeTemplate.category']);
        
        return view('admin.transactions.show', compact('transaction'));
    }

    public function markPaid(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Only pending transactions can be marked as paid.');
            }

            $transaction->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Transaction marked as paid successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction mark as paid failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', 'Failed to mark transaction as paid. Please try again.');
        }
    }

    public function markFailed(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Only pending transactions can be marked as failed.');
            }

            $transaction->update([
                'status' => 'failed',
                'failed_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Transaction marked as failed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction mark as failed failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', 'Failed to mark transaction as failed. Please try again.');
        }
    }

    public function export(Request $request)
    {
        $query = Transaction::with(['alumni.user', 'feeTemplate.feeType', 'feeTemplate.category']);

        // Apply the same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('alumni.user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('alumni', function ($alumniQuery) use ($search) {
                    $alumniQuery->where('matric_number', 'like', "%{$search}%");
                })
                ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fee_type')) {
            $query->whereHas('feeTemplate', function ($q) use ($request) {
                $q->where('fee_type_id', $request->fee_type);
            });
        }

        if ($request->filled('date_range')) {
            $dateRange = $request->date_range;
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $transactions = $query->get();

        $filename = 'transactions_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Reference',
                'Alumni Name',
                'Email',
                'Matric Number',
                'Fee Type',
                'Category',
                'Amount',
                'Status',
                'Created Date',
                'Paid Date',
                'Failed Date'
            ]);

            // Add data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->reference,
                    $transaction->alumni->user->name,
                    $transaction->alumni->user->email,
                    $transaction->alumni->matric_number,
                    $transaction->feeTemplate->feeType->name,
                    $transaction->feeTemplate->category ? $transaction->feeTemplate->category->name : 'N/A',
                    $transaction->amount,
                    $transaction->status,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->paid_at ? $transaction->paid_at->format('Y-m-d H:i:s') : '',
                    $transaction->failed_at ? $transaction->failed_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 