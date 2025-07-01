<?php

namespace App\Http\Controllers\Elcom;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Alumni;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display transaction statistics and recent transactions for ELCOM.
     */
    public function index()
    {
        // Get user statistics
        $totalUsers = User::count();
        $totalOnboardedUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumni');
        })->count();
        
        // Get transaction statistics
        $transactionStats = [
            'total_transactions' => Transaction::count(),
            'paid_transactions' => Transaction::where('status', 'paid')->count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'failed_transactions' => Transaction::where('status', 'failed')->count(),
            'total_amount_paid' => Transaction::where('status', 'paid')->sum('amount'),
        ];
        
        // Get recent transactions with pagination
        $recentTransactions = Transaction::with(['alumni.user', 'feeTemplate.feeType'])
            ->latest()
            ->paginate(20);
            
        return view('elcom.transactions.index', compact(
            'totalUsers',
            'totalOnboardedUsers',
            'transactionStats',
            'recentTransactions'
        ));
    }
} 