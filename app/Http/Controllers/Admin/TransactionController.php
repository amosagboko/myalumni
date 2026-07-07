<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\FeeType;
use App\Services\CredoCentralService;
use App\Services\PaymentCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function __construct(
        protected CredoCentralService $credocentral,
        protected PaymentCompletionService $paymentCompletion
    ) {
    }
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
                ->orWhere('payment_reference', 'like', "%{$search}%");
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

    /**
     * Verify a stuck payment with Credo Central and complete it when paid.
     */
    public function reconcile(Request $request, Transaction $transaction)
    {
        $this->authorize('verify', $transaction);

        $validated = $request->validate([
            'credo_reference' => ['nullable', 'string', 'max:100'],
        ]);

        if ($transaction->isPaid()) {
            return back()->with('info', 'This transaction is already marked as paid.');
        }

        if ($transaction->payment_provider !== 'credocentral') {
            return back()->with('error', 'Reconciliation is only available for Credo Central payments.');
        }

        $credoReference = trim($validated['credo_reference'] ?? '') ?: $transaction->payment_provider_reference;

        if (!$credoReference) {
            return back()->with(
                'error',
                'Credo reference is required. Enter the transRef from the Credo dashboard (e.g. vs_xxxxxxxxxxxx).'
            );
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if (!empty($validated['credo_reference'])) {
                $transaction->update([
                    'payment_provider_reference' => $credoReference,
                ]);
                $transaction->refresh();
            }

            $verification = $this->credocentral->verifyPaymentForAdminReconciliation($transaction, $credoReference);

            if ($verification['paid']) {
                $this->paymentCompletion->complete(
                    $transaction,
                    $verification['paid_at'] ?? null,
                    [
                        'admin_reconciled_at' => now()->toIso8601String(),
                        'admin_reconciled_by' => $request->user()->id,
                        'verification_data' => $verification,
                    ]
                );

                DB::commit();

                return back()->with('success', 'Payment reconciled successfully with Credo Central.');
            }

            if (!empty($verification['is_failed_status']) || strtolower($verification['status']) === 'failed') {
                $transaction->update([
                    'status' => 'failed',
                    'payment_details' => array_merge(
                        is_array($transaction->payment_details) ? $transaction->payment_details : [],
                        [
                            'admin_reconciliation_failed_at' => now()->toIso8601String(),
                            'verification_data' => $verification,
                        ]
                    ),
                ]);

                DB::commit();

                return back()->with('error', 'Credo reports this payment as failed.');
            }

            $transaction->update([
                'payment_details' => array_merge(
                    is_array($transaction->payment_details) ? $transaction->payment_details : [],
                    [
                        'last_admin_reconciliation_at' => now()->toIso8601String(),
                        'verification_data' => $verification,
                    ]
                ),
            ]);

            DB::commit();

            return back()->with('error', $this->reconciliationFeedbackMessage($verification));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin payment reconciliation failed', [
                'transaction_id' => $transaction->id,
                'credo_reference' => $credoReference,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Reconciliation failed: ' . $e->getMessage());
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
                ->orWhere('payment_reference', 'like', "%{$search}%");
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
                    $transaction->payment_reference,
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

    protected function reconciliationFeedbackMessage(array $verification): string
    {
        if (!empty($verification['is_success_status']) && empty($verification['paid'])) {
            $issues = [];

            if (empty($verification['amount_matches'])) {
                $issues[] = sprintf(
                    'amount mismatch (expected ₦%s, Credo returned ₦%s)',
                    number_format((float) ($verification['expected_amount'] ?? 0), 2),
                    isset($verification['returned_amount'])
                        ? number_format((float) $verification['returned_amount'], 2)
                        : 'unknown'
                );
            }

            if (empty($verification['reference_matches'])) {
                $issues[] = sprintf(
                    'reference mismatch (expected %s, Credo returned %s)',
                    $verification['expected_reference'] ?? 'unknown',
                    $verification['business_ref'] ?? 'unknown'
                );
            }

            if ($issues !== []) {
                return 'Credo reports this payment as successful, but local validation failed: '
                    . implode('; ', $issues)
                    . '. Confirm you used the Credo transRef (vs_...), not the DUE-/ALUMNI- reference.';
            }
        }

        return sprintf(
            'Credo still shows this payment as pending or unconfirmed (status: %s, verified with: %s). Confirm the Credo reference matches the successful payment in the Credo dashboard, then try again. Check server logs for the full Credo response.',
            $verification['status'] ?? 'unknown',
            $verification['verified_with_reference'] ?? 'unknown'
        );
    }
} 