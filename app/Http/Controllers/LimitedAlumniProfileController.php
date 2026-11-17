<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\LimitedAlumniRecord;
use App\Models\OnboardingSetting;
use App\Services\AlumniSelfEnrollmentService;
use App\Support\AcademicStructure;
use App\Support\NigeriaLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LimitedAlumniProfileController extends Controller
{
    public const ALLOWED_CATEGORY_SLUGS = [
        AlumniSelfEnrollmentService::CATEGORY_FULL_TIME,
        AlumniSelfEnrollmentService::CATEGORY_PART_TIME,
    ];

    public function __construct(
        protected AlumniSelfEnrollmentService $selfEnrollment
    ) {}

    public function show()
    {
        if (! OnboardingSetting::isEnabled()) {
            return redirect()->route('landing')
                ->with('error', 'Onboarding is currently closed. Please try again later.');
        }

        $record = $this->pendingRecord();

        if (! $record) {
            return redirect()->route('landing')
                ->with('error', 'Your session expired. Search your matriculation number again to continue.');
        }

        if ($record->isCompleted() || Alumni::where('matric_number', $record->matric_number)->exists()) {
            return redirect()->route('landing')
                ->with('error', 'This record has already been completed. Search your matriculation number to set your credentials.');
        }

        $categories = AlumniCategory::active()
            ->whereIn('slug', self::ALLOWED_CATEGORY_SLUGS)
            ->orderBy('name')
            ->get();

        return view('landing.complete-alumni-profile', [
            'record' => $record,
            'academicTree' => AcademicStructure::tree(),
            'nigeriaStates' => NigeriaLocations::statesWithLgas(),
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        if (! OnboardingSetting::isEnabled()) {
            return redirect()->route('landing')
                ->with('error', 'Onboarding is currently closed. Please try again later.');
        }

        $record = $this->pendingRecord();

        if (! $record) {
            return redirect()->route('landing')
                ->with('error', 'Your session expired. Search your matriculation number again to continue.');
        }

        if ($record->isCompleted() || Alumni::where('matric_number', $record->matric_number)->exists()) {
            return redirect()->route('landing')
                ->with('error', 'This record has already been completed. Search your matriculation number to set your credentials.');
        }

        $validated = $request->validate([
            'faculty' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'programme' => 'required|string|max:255',
            'category_slug' => ['required', 'string', Rule::in(self::ALLOWED_CATEGORY_SLUGS)],
            'date_of_birth' => 'required|date|date_format:Y-m-d|before:today',
            'state' => ['required', 'string', Rule::in(NigeriaLocations::states())],
            'lga' => 'required|string|max:100',
            'year_of_entry' => 'required|integer|min:1900|max:'.$record->year_of_graduation,
            'gender' => ['required', Rule::in(['male', 'female'])],
        ]);

        if (! AcademicStructure::isValidCombination($validated['faculty'], $validated['department'], $validated['programme'])) {
            throw ValidationException::withMessages([
                'programme' => 'Select a valid faculty, department, and programme combination.',
            ]);
        }

        if (! NigeriaLocations::isValidPair($validated['state'], $validated['lga'])) {
            throw ValidationException::withMessages([
                'lga' => 'Select a valid LGA for the chosen state.',
            ]);
        }

        try {
            $alumni = DB::transaction(function () use ($record, $validated) {
                $alumni = $this->selfEnrollment->enroll([
                    'matric_number' => $record->matric_number,
                    'name' => $record->fullName(),
                    'department' => $validated['department'],
                    'programme' => $validated['programme'],
                    'faculty' => $validated['faculty'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'state' => $validated['state'],
                    'lga' => $validated['lga'],
                    'year_of_entry' => (int) $validated['year_of_entry'],
                    'year_of_graduation' => (int) $record->year_of_graduation,
                    'gender' => $validated['gender'],
                    'category_slug' => $validated['category_slug'],
                ]);

                $record->markCompleted();

                return $alumni;
            });
        } catch (\Throwable $e) {
            Log::error('Limited alumni profile completion failed', [
                'matric' => $record->matric_number,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        session()->forget('limited_alumni_pending');

        Log::info('Limited alumni profile completed', [
            'alumni_id' => $alumni->id,
            'matric' => $alumni->matric_number,
        ]);

        return app(LandingPageController::class)->credentialsView(
            $alumni->fresh(['user', 'category']),
            'Your details have been saved. Update your email below to set your password and continue onboarding.'
        );
    }

    protected function pendingRecord(): ?LimitedAlumniRecord
    {
        $pending = session('limited_alumni_pending');

        if (! is_array($pending) || empty($pending['id'])) {
            return null;
        }

        return LimitedAlumniRecord::query()->find($pending['id']);
    }
}
