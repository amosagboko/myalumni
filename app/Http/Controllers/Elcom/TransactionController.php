<?php

namespace App\Http\Controllers\Elcom;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Alumni;
use App\Models\Transaction;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display transaction statistics and recent transactions for ELCOM.
     */
    public function index()
    {
        // 1. Total Uploaded Users (all users in the system)
        $totalUploadedUsers = User::count();
        
        // 2. Total Subscribed Users (alumni who paid subscription fee during onboarding)
        $subscriptionFeeType = FeeType::where('code', 'subscription')
            ->orWhere('code', 'subscription_registration')
            ->first();
            
        $totalSubscribedUsers = 0;
        if ($subscriptionFeeType) {
            $totalSubscribedUsers = Transaction::whereHas('feeTemplate', function ($query) use ($subscriptionFeeType) {
                $query->where('fee_type_id', $subscriptionFeeType->id);
            })->where('status', 'paid')->count();
        }
        
        // 3. Total EOI (Expression of Interest payments)
        $eoiFeeType = FeeType::where('code', 'eoi')
            ->orWhere('name', 'like', '%Expression of Interest%')
            ->first();
            
        $totalEOI = 0;
        if ($eoiFeeType) {
            $totalEOI = Transaction::whereHas('feeTemplate', function ($query) use ($eoiFeeType) {
                $query->where('fee_type_id', $eoiFeeType->id);
            })->where('status', 'paid')->count();
        }
        
        // 4. Total Transactions
        $totalTransactions = Transaction::count();
        
        // 5. Paid Transactions
        $paidTransactions = Transaction::where('status', 'paid')->count();
        
        // 6. Pending Transactions
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        
        // 7. Failed Transactions
        $failedTransactions = Transaction::where('status', 'failed')->count();
        
        // 8. Total Amount Paid
        $totalAmountPaid = Transaction::where('status', 'paid')->sum('amount');
        
        // Get recent transactions
        $recentTransactions = Transaction::with(['alumni.user', 'feeTemplate.feeType'])
            ->latest()
            ->paginate(20);
            
        return view('elcom.transactions.index', compact(
            'totalUploadedUsers',
            'totalSubscribedUsers',
            'totalEOI',
            'totalTransactions',
            'paidTransactions',
            'pendingTransactions',
            'failedTransactions',
            'totalAmountPaid',
            'recentTransactions'
        ));
    }
} 