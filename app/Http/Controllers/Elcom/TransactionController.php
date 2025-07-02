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
        $totalSubscribedAmount = 0;
        if ($subscriptionFeeType) {
            $totalSubscribedUsers = Transaction::whereHas('feeTemplate', function ($query) use ($subscriptionFeeType) {
                $query->where('fee_type_id', $subscriptionFeeType->id);
            })->where('status', 'paid')->count();
            
            $totalSubscribedAmount = Transaction::whereHas('feeTemplate', function ($query) use ($subscriptionFeeType) {
                $query->where('fee_type_id', $subscriptionFeeType->id);
            })->where('status', 'paid')->sum('amount');
        }
        
        // 3. Total EOI (Expression of Interest payments)
        $eoiFeeTypeIds = FeeType::where('code', 'like', 'eoi-%')->pluck('id');
        $totalEOI = 0;
        $totalEOIAmount = 0;
        if ($eoiFeeTypeIds->isNotEmpty()) {
            $totalEOI = Transaction::whereHas('feeTemplate', function ($query) use ($eoiFeeTypeIds) {
                $query->whereIn('fee_type_id', $eoiFeeTypeIds);
            })->where('status', 'paid')->count();
            
            $totalEOIAmount = Transaction::whereHas('feeTemplate', function ($query) use ($eoiFeeTypeIds) {
                $query->whereIn('fee_type_id', $eoiFeeTypeIds);
            })->where('status', 'paid')->sum('amount');
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
        
        // 9. Special Exemption (2024 graduates who have completed bio data and are exempted from all fees)
        $specialExemption = Alumni::where('year_of_graduation', 2024)
            ->whereNotNull('contact_address')
            ->whereNotNull('phone_number')
            ->whereNotNull('qualification_type')
            ->count();
        
        // 10. Voters Register (Total Subscribed Users + Special Exemption)
        $votersRegister = $totalSubscribedUsers + $specialExemption;
        
        // Get recent transactions
        $recentTransactions = Transaction::with(['alumni.user', 'feeTemplate.feeType'])
            ->latest()
            ->paginate(20);
            
        return view('elcom.transactions.index', compact(
            'totalUploadedUsers',
            'totalSubscribedUsers',
            'totalSubscribedAmount',
            'totalEOI',
            'totalEOIAmount',
            'totalTransactions',
            'paidTransactions',
            'pendingTransactions',
            'failedTransactions',
            'totalAmountPaid',
            'specialExemption',
            'votersRegister',
            'recentTransactions'
        ));
    }
} 