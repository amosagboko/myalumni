<?php

namespace App\Http\Controllers;

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
            
        return view('admin.categories.index', compact('categories'));
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
                ->route('admin.categories.index')
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
     * Show the form for editing the specified category.
     */
    public function edit(AlumniCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, AlumniCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:alumni_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $validated['slug'] = Str::slug($validated['name']);
            
            $category->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.categories.index')
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
    public function destroy(AlumniCategory $category)
    {
        try {
            // Check if category has any alumni
            if ($category->alumni()->exists()) {
                return back()->with('error', 'Cannot delete category that has alumni assigned to it.');
            }

            // Check if category has any transaction fees
            if ($category->transactionFees()->exists()) {
                return back()->with('error', 'Cannot delete category that has transaction fees.');
            }

            $category->delete();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Category deletion failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete category. Please try again.');
        }
    }
} 