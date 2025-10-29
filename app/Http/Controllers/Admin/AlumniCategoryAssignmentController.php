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
        // Use Livewire component instead
        return view('admin.alumni-categories.assign');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'alumni_id' => 'required|exists:alumni,id',
            'category_id' => 'required|exists:alumni_categories,id'
        ]);

        try {
            DB::beginTransaction();

            $alumni = Alumni::with('category')->findOrFail($request->alumni_id);
            $newCategory = AlumniCategory::findOrFail($request->category_id);
            $oldCategorySlug = $alumni->category ? $alumni->category->slug : null;
            
            // Update category
            $alumni->update(['category_id' => $request->category_id]);
            
            // Handle qualification_type consistency
            // If switching to/from postgraduate, validate/reset qualification_type
            if ($newCategory->slug === 'postgraduate') {
                // Switching TO postgraduate category
                $validQualificationTypes = ['PhD', 'MSc', 'PGD'];
                $currentQualificationType = $alumni->qualification_type;
                
                // If qualification_type exists but is invalid for postgraduate, log warning
                if ($currentQualificationType && !in_array($currentQualificationType, $validQualificationTypes)) {
                    Log::warning('Postgraduate category assigned but qualification_type is invalid', [
                        'alumni_id' => $alumni->id,
                        'category_id' => $request->category_id,
                        'current_qualification_type' => $currentQualificationType,
                        'note' => 'Alumni will need to update qualification_type to PhD, MSc, or PGD during bio data completion'
                    ]);
                }
            } elseif ($oldCategorySlug === 'postgraduate' && $newCategory->slug !== 'postgraduate') {
                // Switching FROM postgraduate to non-postgraduate category
                $validQualificationTypes = ['Degree', 'Diploma', 'Certificate'];
                $currentQualificationType = $alumni->qualification_type;
                
                // If qualification_type is PhD/MSc/PGD but category is no longer postgraduate, clear it
                if ($currentQualificationType && in_array($currentQualificationType, ['PhD', 'MSc', 'PGD'])) {
                    $alumni->update(['qualification_type' => null]);
                    Log::info('Qualification type cleared when switching from postgraduate to non-postgraduate category', [
                        'alumni_id' => $alumni->id,
                        'old_category' => $oldCategorySlug,
                        'new_category' => $newCategory->slug,
                        'cleared_qualification_type' => $currentQualificationType
                    ]);
                }
            }
            
            DB::commit();

            Log::info('Category assigned successfully', [
                'alumni_id' => $alumni->id,
                'old_category' => $oldCategorySlug,
                'new_category' => $newCategory->slug,
                'qualification_type' => $alumni->fresh()->qualification_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category assigned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

            $newCategory = AlumniCategory::findOrFail($request->category_id);
            $alumniRecords = Alumni::with('category')->whereIn('id', $request->alumni_ids)->get();
            
            $updatedCount = 0;
            $warningCount = 0;
            
            foreach ($alumniRecords as $alumni) {
                $oldCategorySlug = $alumni->category ? $alumni->category->slug : null;
                
                // Update category
                $alumni->update(['category_id' => $request->category_id]);
                $updatedCount++;
                
                // Handle qualification_type consistency for each alumni
                if ($newCategory->slug === 'postgraduate') {
                    // Switching TO postgraduate category
                    $validQualificationTypes = ['PhD', 'MSc', 'PGD'];
                    $currentQualificationType = $alumni->qualification_type;
                    
                    if ($currentQualificationType && !in_array($currentQualificationType, $validQualificationTypes)) {
                        $warningCount++;
                        Log::warning('Postgraduate category assigned but qualification_type is invalid', [
                            'alumni_id' => $alumni->id,
                            'current_qualification_type' => $currentQualificationType
                        ]);
                    }
                } elseif ($oldCategorySlug === 'postgraduate' && $newCategory->slug !== 'postgraduate') {
                    // Switching FROM postgraduate to non-postgraduate category
                    if ($alumni->qualification_type && in_array($alumni->qualification_type, ['PhD', 'MSc', 'PGD'])) {
                        $alumni->update(['qualification_type' => null]);
                    }
                }
            }

            DB::commit();

            $message = "Successfully assigned category to {$updatedCount} alumni.";
            if ($warningCount > 0) {
                $message .= " Note: {$warningCount} alumni may need to update their qualification type to PhD, MSc, or PGD.";
            }

            Log::info('Bulk category assignment completed', [
                'updated_count' => $updatedCount,
                'warning_count' => $warningCount,
                'category_slug' => $newCategory->slug
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk category assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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