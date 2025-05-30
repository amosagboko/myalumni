<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    public function transactions()
    {
        $transactions = Transaction::with(['alumni.user', 'feeTemplate'])
            ->latest()
            ->paginate(20);

        return view('admin.statistics.transactions', compact('transactions'));
    }

    public function alumniDistribution()
    {
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

        return view('admin.statistics.alumni-distribution', compact('alumniByYear', 'alumniByFaculty'));
    }
} 