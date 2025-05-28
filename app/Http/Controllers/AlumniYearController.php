<?php

namespace App\Http\Controllers;

use App\Models\AlumniYear;
use Illuminate\Http\Request;

class AlumniYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alumniYears = AlumniYear::orderBy('year', 'desc')->paginate(10);
        return view('alumni-years.index', compact('alumniYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('alumni-years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'sometimes|boolean'
        ]);

        // Set is_active to false if not present in request
        $validated['is_active'] = $request->boolean('is_active', false);

        if ($validated['is_active']) {
            // Deactivate all other years
            AlumniYear::where('is_active', true)->update(['is_active' => false]);
        }

        AlumniYear::create($validated);

        return redirect()->route('alumni-years.index')
            ->with('success', 'Alumni year created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AlumniYear $alumniYear)
    {
        return view('alumni-years.show', compact('alumniYear'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlumniYear $alumniYear)
    {
        return view('alumni-years.edit', compact('alumniYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlumniYear $alumniYear)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        if ($validated['is_active']) {
            // Deactivate all other years
            AlumniYear::where('is_active', true)
                ->where('id', '!=', $alumniYear->id)
                ->update(['is_active' => false]);
        }

        $alumniYear->update($validated);

        return redirect()->route('alumni-years.index')
            ->with('success', 'Alumni year updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlumniYear $alumniYear)
    {
        if ($alumniYear->hasFees()) {
            return redirect()->route('alumni-years.index')
                ->with('error', 'Cannot delete alumni year with associated fees.');
        }

        $alumniYear->delete();

        return redirect()->route('alumni-years.index')
            ->with('success', 'Alumni year deleted successfully.');
    }

    /**
     * Activate the specified alumni year.
     */
    public function activate(AlumniYear $alumniYear)
    {
        // Deactivate all other years
        AlumniYear::where('is_active', true)
            ->where('id', '!=', $alumniYear->id)
            ->update(['is_active' => false]);

        $alumniYear->update(['is_active' => true]);

        return redirect()->route('alumni-years.index')
            ->with('success', 'Alumni year activated successfully.');
    }

    /**
     * Deactivate the specified alumni year.
     */
    public function deactivate(AlumniYear $alumniYear)
    {
        $alumniYear->update(['is_active' => false]);

        return redirect()->route('alumni-years.index')
            ->with('success', 'Alumni year deactivated successfully.');
    }
} 