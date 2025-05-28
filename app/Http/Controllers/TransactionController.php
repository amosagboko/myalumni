<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\FeeTemplate;
use App\Models\AlumniYear;
use App\Models\User;
use App\Http\Requests\TransactionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use HasRoles, AuthorizesRequests;

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->authorizeResource(Transaction::class, 'transaction');
    }

    public function index()
    {
        $this->authorize('viewAny', Transaction::class);

        $query = Transaction::with(['alumni', 'feeTemplate.feeType', 'feeTemplate.category'])
            ->when(!$this->authorize('viewAll', Transaction::class), function ($query) {
                $query->whereHas('alumni', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            })
            ->latest();

        $transactions = $query->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $currentYear = AlumniYear::where('is_active', true)->first();
        $fees = FeeTemplate::with(['category', 'feeType'])
            ->where('graduation_year', $currentYear->year)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->where('valid_from', '<=', now())
            ->get();

        return view('transactions.create', compact('fees', 'currentYear'));
    }

    public function store(TransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $fee = FeeTemplate::findOrFail($request->fee_id);
            
            // Verify amount matches fee
            if ($request->amount != $fee->amount) {
                return back()->with('error', 'Payment amount does not match fee amount.');
            }

            // Verify fee is active and valid
            if (!$fee->isValid()) {
                return back()->with('error', 'This fee is no longer active or valid.');
            }

            // Check for existing pending transaction
            $existingTransaction = Transaction::where('alumni_id', Auth::user()->alumni->id)
                ->where('fee_template_id', $fee->id)
                ->where('status', 'pending')
                ->first();

            if ($existingTransaction) {
                return back()->with('error', 'You already have a pending transaction for this fee.');
            }

            // Create the transaction
            $transaction = Transaction::create([
                'alumni_id' => Auth::user()->alumni->id,
                'fee_template_id' => $fee->id,
                'amount' => $fee->amount,
                'status' => 'pending',
                'payment_reference' => $request->payment_reference,
                'payment_provider' => 'paystack', // Default provider
                'payment_details' => [
                    'fee_type' => $fee->feeType->name,
                    'fee_description' => $fee->description,
                    'graduation_year' => $fee->graduation_year,
                    'category' => $fee->category->name
                ]
            ]);

            DB::commit();

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaction created successfully. Please proceed with payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create transaction. Please try again.');
        }
    }

    public function show(Transaction $transaction)
    {
        // Load necessary relationships
        $transaction->load(['alumni', 'feeTemplate.feeType', 'feeTemplate.category']);

        return view('transactions.show', compact('transaction'));
    }

    public function markAsPaid(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            if ($transaction->status !== 'pending') {
                throw new \Exception('Only pending transactions can be marked as paid.');
            }

            $transaction->markAsCompleted();

            DB::commit();

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaction marked as paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction payment marking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function markAsFailed(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            if ($transaction->status !== 'pending') {
                throw new \Exception('Only pending transactions can be marked as failed.');
            }

            $transaction->markAsFailed();

            DB::commit();

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaction marked as failed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failure marking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function verify(Transaction $transaction)
    {
        $this->authorize('verify', $transaction);

        // ... rest of the method ...
    }
} 