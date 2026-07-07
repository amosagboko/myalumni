<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\FeeType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    public function transactions(Request $request)
    {
        $query = Transaction::with(['alumni.user', 'feeTemplate.feeType', 'feeTemplate.category']);

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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total' => Transaction::count(),
            'paid' => Transaction::where('status', 'paid')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
        ];

        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();

        return view('admin.statistics.transactions', compact('transactions', 'stats', 'feeTypes'));
    }

    public function alumniDistribution()
    {
        $alumniByYear = Alumni::select('year_of_graduation', DB::raw('count(*) as total'))
            ->groupBy('year_of_graduation')
            ->orderBy('year_of_graduation', 'desc')
            ->get();

        $alumniByFaculty = Alumni::select('faculty', DB::raw('count(*) as total'))
            ->groupBy('faculty')
            ->orderBy('total', 'desc')
            ->get();

        $totalAlumni = Alumni::count();

        return view('admin.statistics.alumni-distribution', compact('alumniByYear', 'alumniByFaculty', 'totalAlumni'));
    }
}
