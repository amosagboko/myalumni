<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Alumni;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get total users count by role
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();
        
        // Get alumni statistics
        $totalAlumni = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumni');
        })->count();
        
        // Get payment statistics
        $paymentStats = [
            'total_transactions' => Transaction::count(),
            'paid_transactions' => Transaction::where('status', 'paid')->count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'failed_transactions' => Transaction::where('status', 'failed')->count(),
            'total_amount' => Transaction::where('status', 'paid')->sum('amount'),
        ];
        
        // Get recent transactions
        $recentTransactions = Transaction::with(['alumni.user', 'feeTemplate'])
            ->latest()
            ->take(5)
            ->get();
            
        // Get alumni by graduation year
        $alumniByYear = Alumni::select('year_of_graduation', DB::raw('count(*) as total'))
            ->groupBy('year_of_graduation')
            ->orderBy('year_of_graduation', 'desc')
            ->get();
            
        // Get alumni by faculty
        $alumniByFaculty = Alumni::select('faculty', DB::raw('count(*) as total'))
            ->groupBy('faculty')
            ->orderBy('total', 'desc')
            ->get();

        return view('admin.home', compact(
            'totalUsers',
            'activeUsers',
            'suspendedUsers',
            'totalAlumni',
            'paymentStats',
            'recentTransactions',
            'alumniByYear',
            'alumniByFaculty'
        ));
    }
} 