<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlumniCategoryAssignmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Alumni::with(['user', 'category']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('matric_number', 'like', "%{$search}%");
            });
        }

        // Apply faculty filter
        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        // Apply graduation year filter
        if ($request->filled('graduation_year')) {
            $query->where('year_of_graduation', $request->graduation_year);
        }

        // Apply category filter
        if ($request->filled('category')) {
            if ($request->category === 'unassigned') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $request->category);
            }
        }

        $alumni = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options - handle empty collections gracefully
        $faculties = Alumni::distinct()->pluck('faculty')->filter()->sort();
        $graduationYears = Alumni::distinct()->pluck('year_of_graduation')->filter()->sort()->reverse();
        $categories = AlumniCategory::where('is_active', true)->get();

            return view('admin.alumni-categories.assign', compact('alumni', 'faculties', 'graduationYears', 'categories'));
        } catch (\Exception $e) {
            Log::error('Error in AlumniCategoryAssignmentController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty data to prevent 500 error
            $alumni = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            $faculties = collect();
            $graduationYears = collect();
            $categories = collect();
            
            return view('admin.alumni-categories.assign', compact('alumni', 'faculties', 'graduationYears', 'categories'))
                ->with('error', 'There was an issue loading the alumni data. Please check the error logs.');
        }
    }

    public function assign(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumni,id',
            'category_id' => 'required|exists:alumni_categories,id'
        ]);

        try {
            DB::beginTransaction();

            $alumni = Alumni::findOrFail($request->alumni_id);
            $alumni->update(['category_id' => $request->category_id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category assigned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category assignment failed', [
                'error' => $e->getMessage(),
                'alumni_id' => $request->alumni_id,
                'category_id' => $request->category_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign category. Please try again.'
            ], 500);
        }
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'alumni_ids' => 'required|array|min:1',
            'alumni_ids.*' => 'exists:alumni,id',
            'category_id' => 'required|exists:alumni_categories,id'
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = Alumni::whereIn('id', $request->alumni_ids)
                ->update(['category_id' => $request->category_id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned category to {$updatedCount} alumni."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk category assignment failed', [
                'error' => $e->getMessage(),
                'alumni_ids' => $request->alumni_ids,
                'category_id' => $request->category_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign categories. Please try again.'
            ], 500);
        }
    }

    public function removeCategory(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumni,id'
        ]);

        try {
            DB::beginTransaction();

            $alumni = Alumni::findOrFail($request->alumni_id);
            $alumni->update(['category_id' => null]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category removed successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category removal failed', [
                'error' => $e->getMessage(),
                'alumni_id' => $request->alumni_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove category. Please try again.'
            ], 500);
        }
    }

    public function bulkRemoveCategory(Request $request)
    {
        $request->validate([
            'alumni_ids' => 'required|array|min:1',
            'alumni_ids.*' => 'exists:alumni,id'
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = Alumni::whereIn('id', $request->alumni_ids)
                ->update(['category_id' => null]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully removed category from {$updatedCount} alumni."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk category removal failed', [
                'error' => $e->getMessage(),
                'alumni_ids' => $request->alumni_ids
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk remove categories. Please try again.'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $query = Alumni::with(['user', 'category']);

        // Apply the same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('matric_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        if ($request->filled('graduation_year')) {
            $query->where('year_of_graduation', $request->graduation_year);
        }

        if ($request->filled('category')) {
            if ($request->category === 'unassigned') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $request->category);
            }
        }

        $alumni = $query->get();

        $filename = 'alumni_categories_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($alumni) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Name',
                'Email',
                'Matric Number',
                'Faculty',
                'Department',
                'Programme',
                'Graduation Year',
                'Category',
                'Category Description'
            ]);

            // Add data
            foreach ($alumni as $alumnus) {
                fputcsv($file, [
                    $alumnus->user->name,
                    $alumnus->user->email,
                    $alumnus->matric_number,
                    $alumnus->faculty,
                    $alumnus->department,
                    $alumnus->programme,
                    $alumnus->year_of_graduation,
                    $alumnus->category ? $alumnus->category->name : 'Unassigned',
                    $alumnus->category ? $alumnus->category->description : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 