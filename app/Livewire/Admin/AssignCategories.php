<?php

namespace App\Livewire\Admin;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AssignCategories extends Component
{
    use WithPagination;

    public $search = '';
    public $faculty = '';
    public $graduationYear = '';
    public $category = '';
    
    public $selectedAlumni = [];
    public $bulkCategoryId = '';
    
    public $alumniId = null;
    public $categoryId = null;
    
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFaculty()
    {
        $this->resetPage();
    }

    public function updatingGraduationYear()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->faculty = '';
        $this->graduationYear = '';
        $this->category = '';
        $this->resetPage();
    }

    public function assignCategory($alumniId, $categoryId)
    {
        if (empty($categoryId)) {
            return; // Don't do anything if no category selected
        }

        try {
            DB::beginTransaction();

            $alumni = Alumni::with('category')->findOrFail($alumniId);
            $newCategory = AlumniCategory::findOrFail($categoryId);
            $oldCategorySlug = $alumni->category ? $alumni->category->slug : null;
            
            // Update category
            $alumni->update(['category_id' => $categoryId]);
            
            // Handle qualification_type consistency
            if ($newCategory->slug === 'postgraduate') {
                // Switching TO postgraduate category
                $validQualificationTypes = ['PhD', 'MSc', 'PGD'];
                $currentQualificationType = $alumni->qualification_type;
                
                if ($currentQualificationType && !in_array($currentQualificationType, $validQualificationTypes)) {
                    Log::warning('Postgraduate category assigned but qualification_type is invalid', [
                        'alumni_id' => $alumni->id,
                        'category_id' => $categoryId,
                        'current_qualification_type' => $currentQualificationType,
                    ]);
                }
            } elseif ($oldCategorySlug === 'postgraduate' && $newCategory->slug !== 'postgraduate') {
                // Switching FROM postgraduate to non-postgraduate category
                if ($alumni->qualification_type && in_array($alumni->qualification_type, ['PhD', 'MSc', 'PGD'])) {
                    $alumni->update(['qualification_type' => null]);
                    Log::info('Qualification type cleared when switching from postgraduate to non-postgraduate category', [
                        'alumni_id' => $alumni->id,
                        'old_category' => $oldCategorySlug,
                        'new_category' => $newCategory->slug,
                    ]);
                }
            }
            
            DB::commit();

            Log::info('Category assigned successfully', [
                'alumni_id' => $alumni->id,
                'old_category' => $oldCategorySlug,
                'new_category' => $newCategory->slug,
            ]);

            session()->flash('success', 'Category assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'alumni_id' => $alumniId,
                'category_id' => $categoryId
            ]);

            session()->flash('error', 'Failed to assign category: ' . $e->getMessage());
        }
    }

    public function bulkAssign()
    {
        if (empty($this->selectedAlumni)) {
            session()->flash('error', 'Please select at least one alumni.');
            return;
        }

        if (empty($this->bulkCategoryId)) {
            session()->flash('error', 'Please select a category.');
            return;
        }

        try {
            DB::beginTransaction();

            $newCategory = AlumniCategory::findOrFail($this->bulkCategoryId);
            $alumniRecords = Alumni::with('category')->whereIn('id', $this->selectedAlumni)->get();
            
            $updatedCount = 0;
            $warningCount = 0;
            
            foreach ($alumniRecords as $alumni) {
                $oldCategorySlug = $alumni->category ? $alumni->category->slug : null;
                
                // Update category
                $alumni->update(['category_id' => $this->bulkCategoryId]);
                $updatedCount++;
                
                // Handle qualification_type consistency
                if ($newCategory->slug === 'postgraduate') {
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

            session()->flash('success', $message);
            
            // Clear selections
            $this->selectedAlumni = [];
            $this->bulkCategoryId = '';
            
            // Reset the page
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk category assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'alumni_ids' => $this->selectedAlumni,
                'category_id' => $this->bulkCategoryId
            ]);

            session()->flash('error', 'Failed to bulk assign categories: ' . $e->getMessage());
        }
    }

    public function toggleSelectAll()
    {
        $currentPageAlumni = $this->getAlumniQuery()->paginate(20)->pluck('id')->toArray();
        
        // Check if all current page alumni are selected
        $allSelected = !empty($currentPageAlumni) && count(array_intersect($this->selectedAlumni, $currentPageAlumni)) === count($currentPageAlumni);
        
        if ($allSelected) {
            // Deselect all current page alumni
            $this->selectedAlumni = array_diff($this->selectedAlumni, $currentPageAlumni);
        } else {
            // Select all current page alumni (merge without duplicates)
            $this->selectedAlumni = array_unique(array_merge($this->selectedAlumni, $currentPageAlumni));
        }
    }

    public function removeCategory($alumniId)
    {
        try {
            DB::beginTransaction();

            $alumni = Alumni::findOrFail($alumniId);
            $alumni->update(['category_id' => null]);

            DB::commit();

            session()->flash('success', 'Category removed successfully.');
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category removal failed', [
                'error' => $e->getMessage(),
                'alumni_id' => $alumniId
            ]);

            session()->flash('error', 'Failed to remove category: ' . $e->getMessage());
        }
    }

    protected function getAlumniQuery()
    {
        $query = Alumni::with(['user', 'category']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', "%{$this->search}%")
                             ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->orWhere('matric_number', 'like', "%{$this->search}%");
            });
        }

        // Apply faculty filter
        if ($this->faculty) {
            $query->where('faculty', $this->faculty);
        }

        // Apply graduation year filter
        if ($this->graduationYear) {
            $query->where('year_of_graduation', $this->graduationYear);
        }

        // Apply category filter
        if ($this->category) {
            if ($this->category === 'unassigned') {
                $query->whereNull('category_id');
            } else {
                $query->where('category_id', $this->category);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $alumni = $this->getAlumniQuery()->paginate(20);
        
        // Get filter options
        $faculties = Alumni::distinct()->pluck('faculty')->filter()->sort();
        $graduationYears = Alumni::distinct()->pluck('year_of_graduation')->filter()->sort()->reverse();
        $categories = AlumniCategory::where('is_active', true)->get();

        return view('livewire.admin.assign-categories', [
            'alumni' => $alumni,
            'faculties' => $faculties,
            'graduationYears' => $graduationYears,
            'categories' => $categories,
        ]);
    }
}

