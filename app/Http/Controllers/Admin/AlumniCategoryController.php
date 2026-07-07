<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlumniCategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = AlumniCategory::withCount('alumni')
            ->orderBy('name')
            ->paginate(10);

        $stats = [
            'total' => AlumniCategory::count(),
            'active' => AlumniCategory::where('is_active', true)->count(),
            'inactive' => AlumniCategory::where('is_active', false)->count(),
            'assigned' => AlumniCategory::has('alumni')->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alumni_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $validated['slug'] = Str::slug($validated['name']);
            
            AlumniCategory::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.alumni-categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category creation failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create category. Please try again.');
        }
    }

    /**
     * Display the specified category.
     */
    public function show(AlumniCategory $alumniCategory)
    {
        $alumniCategory->loadCount('alumni');
        return view('admin.categories.show', compact('alumniCategory'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(AlumniCategory $alumniCategory)
    {
        return view('admin.categories.edit', compact('alumniCategory'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, AlumniCategory $alumniCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alumni_categories,name,' . $alumniCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $validated['slug'] = Str::slug($validated['name']);
            
            $alumniCategory->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.alumni-categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category update failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update category. Please try again.');
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(AlumniCategory $alumniCategory)
    {
        try {
            DB::beginTransaction();

            // Check if category has alumni
            if ($alumniCategory->alumni()->count() > 0) {
                return back()->with('error', 'Cannot delete category that has alumni assigned to it.');
            }

            $alumniCategory->delete();

            DB::commit();

            return redirect()
                ->route('admin.alumni-categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete category. Please try again.');
        }
    }
} 