<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\FeeTemplate;
use App\Models\AlumniYear;
use App\Models\CategoryTransactionFee;
use App\Http\Requests\TransactionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['user', 'categoryTransactionFee.alumniYear'])
            ->when(!Auth::user()->isAdmin(), function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();
            
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $currentYear = AlumniYear::where('is_active', true)->first();
        $fees = CategoryTransactionFee::with(['category', 'alumniYear'])
            ->where('alumni_year_id', $currentYear->id)
            ->where('is_active', true)
            ->get();

        return view('transactions.create', compact('fees', 'currentYear'));
    }

    public function store(TransactionRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $fee = CategoryTransactionFee::findOrFail($request->fee_id);
            
            // Verify amount matches fee
            if ($request->amount != $fee->amount) {
                return back()->with('error', 'Payment amount does not match fee amount.');
            }

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'category_transaction_fee_id' => $request->fee_id,
                'amount' => $request->amount,
                'payment_reference' => $request->payment_reference,
                'status' => 'pending'
            ]);
            
            DB::commit();
            return redirect()->route('transactions.index')
                ->with('success', 'Transaction created successfully. Please wait for admin verification.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create transaction. Please try again.');
        }
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }

    public function verify(Transaction $transaction)
    {
        $this->authorize('verify', $transaction);

        try {
            DB::beginTransaction();
            
            $transaction->update([
                'status' => 'verified',
                'paid_at' => now()
            ]);
            
            DB::commit();
            return redirect()->route('transactions.index')
                ->with('success', 'Transaction verified successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to verify transaction. Please try again.');
        }
    }

    public function reject(Transaction $transaction)
    {
        $this->authorize('verify', $transaction);

        try {
            DB::beginTransaction();
            
            $transaction->update([
                'status' => 'rejected'
            ]);
            
            DB::commit();
            return redirect()->route('transactions.index')
                ->with('success', 'Transaction rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject transaction. Please try again.');
        }
    }
} 