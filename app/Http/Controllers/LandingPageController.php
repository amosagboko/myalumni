<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Event;
use App\Models\OnboardingSetting;
use App\Services\AlumniSelfEnrollmentService;
use App\Support\NigeriaLocations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LandingPageController extends Controller
{
    public function __construct(
        protected AlumniSelfEnrollmentService $selfEnrollment
    ) {}

    private function isOnboardingAllowed(): bool
    {
        return OnboardingSetting::isEnabled();
    }

    private function isSelfEnrollmentAllowed(): bool
    {
        return OnboardingSetting::isSelfEnrollmentEnabled();
    }

    /**
     * Credential actions for an existing alumni account.
     * Uploaded onboarding OR (2026+ self-enroll cohort when that toggle is on).
     */
    private function canAccessExistingCredentials(Alumni $alumni): bool
    {
        if ($this->isOnboardingAllowed()) {
            return true;
        }

        return $this->isSelfEnrollmentAllowed()
            && (int) $alumni->year_of_graduation >= (int) config('fulafia.self_enrollment_graduation_year', 2026);
    }

    public function index()
    {
        $connectItems = Event::published()
            ->ofType('connect')
            ->ordered()
            ->limit(6)
            ->get();

        $eventItems = Event::published()
            ->ofType('event')
            ->ordered()
            ->limit(6)
            ->get();

        $opportunityItems = Event::published()
            ->ofType('opportunity')
            ->ordered()
            ->limit(6)
            ->get();

        $selfEnrollmentOpen = $this->isSelfEnrollmentAllowed();
        $onboardingOpen = $this->isOnboardingAllowed();

        return view('landing', compact(
            'connectItems',
            'eventItems',
            'opportunityItems',
            'selfEnrollmentOpen',
            'onboardingOpen'
        ));
    }

    public function searchCredentials(Request $request)
    {
        $request->validate([
            'matriculation_id' => 'required|string|max:50',
        ]);

        $matric = trim($request->matriculation_id);
        $alumni = Alumni::with(['user', 'category'])->where('matric_number', $matric)->first();

        if ($alumni) {
            if (! $this->canAccessExistingCredentials($alumni)) {
                return redirect()->back()
                    ->with('error', 'Onboarding and self-enrollment are currently closed. Please try again later.');
            }

            return $this->credentialsView($alumni);
        }

        // Not uploaded — attempt 2026+ self-enrollment path
        if (! $this->isSelfEnrollmentAllowed()) {
            return redirect()->back()
                ->with('error', 'No alumni found with this matriculation number. Self-enrollment for 2026+ graduates is currently closed.');
        }

        try {
            $student = $this->selfEnrollment->lookupByMatric($matric);
        } catch (\Throwable $e) {
            Log::info('Self-enrollment lookup failed', [
                'matric' => $matric,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }

        $apiMatric = trim((string) ($student['userId'] ?? $matric));

        // Race: another request may have created the record
        $existing = Alumni::with(['user', 'category'])->where('matric_number', $apiMatric)->first();
        if ($existing) {
            return $this->credentialsView($existing);
        }

        $pending = [
            'matric_number' => $apiMatric,
            'name' => (string) ($student['name'] ?? ''),
            'department' => (string) ($student['department'] ?? ''),
            'phone_number' => (string) ($student['gsm'] ?? ''),
            'student_email' => (string) ($student['email'] ?? ''),
            'year_of_entry' => $this->selfEnrollment->guessYearOfEntry($apiMatric),
            'year_of_graduation' => (int) config('fulafia.self_enrollment_graduation_year', date('Y')),
            'category_label' => 'Undergraduate (Full-time)',
        ];

        session(['self_enroll_pending' => $pending]);

        return redirect()->route('landing.self-enroll.confirm');
    }

    public function showSelfEnrollConfirm()
    {
        if (! $this->isSelfEnrollmentAllowed()) {
            return redirect()->route('landing')
                ->with('error', 'Self-enrollment for 2026+ graduates is currently closed.');
        }

        $pending = session('self_enroll_pending');

        if (! is_array($pending) || empty($pending['matric_number'])) {
            return redirect()->route('landing')
                ->with('error', 'Please search with your matriculation number to begin self-enrollment.');
        }

        return view('landing.self-enroll-confirm', [
            'pending' => $pending,
            'nigeriaStates' => NigeriaLocations::statesWithLgas(),
        ]);
    }

    public function submitSelfEnrollConfirm(Request $request)
    {
        if (! $this->isSelfEnrollmentAllowed()) {
            return redirect()->route('landing')
                ->with('error', 'Self-enrollment for 2026+ graduates is currently closed.');
        }

        $pending = session('self_enroll_pending');

        if (! is_array($pending) || empty($pending['matric_number'])) {
            return redirect()->route('landing')
                ->with('error', 'Your self-enrollment session expired. Please search again.');
        }

        $validated = $request->validate([
            'programme' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'state' => ['required', 'string', Rule::in(NigeriaLocations::states())],
            'lga' => 'required|string|max:100',
            'gender' => ['required', Rule::in(['male', 'female'])],
            'phone_number' => 'nullable|string|max:30',
        ]);

        if (! NigeriaLocations::isValidPair($validated['state'], $validated['lga'])) {
            throw ValidationException::withMessages([
                'lga' => 'Select a valid LGA for the chosen state.',
            ]);
        }

        $yearOfEntry = $pending['year_of_entry'] ?? null;
        if (! is_numeric($yearOfEntry)) {
            throw ValidationException::withMessages([
                'year_of_entry' => 'Year of entry could not be determined from your matriculation number. Please contact support.',
            ]);
        }

        $phoneFromApi = trim((string) ($pending['phone_number'] ?? ''));
        $phoneNumber = trim((string) ($validated['phone_number'] ?? ''));
        if ($phoneNumber === '') {
            $phoneNumber = $phoneFromApi !== '' ? $phoneFromApi : null;
        }

        try {
            $alumni = $this->selfEnrollment->enroll([
                'matric_number' => $pending['matric_number'],
                'name' => $pending['name'] ?: 'Alumni',
                'department' => $pending['department'] ?: 'To be updated',
                'programme' => $validated['programme'],
                'faculty' => $validated['faculty'],
                'date_of_birth' => $validated['date_of_birth'],
                'state' => $validated['state'],
                'lga' => $validated['lga'],
                'year_of_entry' => (int) $yearOfEntry,
                'year_of_graduation' => (int) ($pending['year_of_graduation'] ?? config('fulafia.self_enrollment_graduation_year', date('Y'))),
                'gender' => $validated['gender'],
                'phone_number' => $phoneNumber,
            ]);
        } catch (\Throwable $e) {
            Log::error('Self-enrollment create failed', [
                'matric' => $pending['matric_number'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        session()->forget('self_enroll_pending');

        Log::info('Alumni self-enrolled', [
            'alumni_id' => $alumni->id,
            'matric' => $alumni->matric_number,
        ]);

        return $this->credentialsView($alumni->fresh(['user', 'category']), 'Your account has been created. Update your email below to set your password and continue onboarding.');
    }

    protected function credentialsView(Alumni $alumni, ?string $flashInfo = null)
    {
        $user = $alumni->user;
        $tempEmail = $this->selfEnrollment->tempEmailFromMatric($alumni->matric_number);

        $message = null;
        if ((int) $alumni->year_of_graduation === 2024) {
            $message = 'As a 2024 graduate, you were not charged dues in your graduation year. Complete your bio data and pay the annual alumni due for the current payment year.';
        } elseif ((int) $alumni->year_of_graduation >= 2025) {
            $message = 'As a 2025+ graduate, you must complete your bio data and pay category-based fees.';
        }

        if ($flashInfo) {
            session()->flash('info', $flashInfo);
        }

        return view('landing.credentials', [
            'alumni' => $alumni,
            'tempEmail' => $tempEmail,
            'name' => $user->name,
            'matriculation_id' => $alumni->matric_number,
            'category' => $alumni->category,
            'message' => $message,
        ]);
    }

    public function updateEmail(Request $request)
    {
        try {
            $request->validate([
                'matriculation_id' => 'required|string',
                'new_email' => 'required|email|unique:users,email',
            ]);

            $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

            if (! $alumni) {
                return response()->json([
                    'success' => false,
                    'message' => 'No alumni found with this matriculation number.',
                ]);
            }

            if (! $this->canAccessExistingCredentials($alumni)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Onboarding and self-enrollment are currently closed. Please try again later.',
                ]);
            }

            $user = $alumni->user;
            $tempEmail = $this->selfEnrollment->tempEmailFromMatric($alumni->matric_number);

            $user->email = $request->new_email;
            $user->save();

            $token = Password::createToken($user);

            Mail::send('emails.alumni-welcome', [
                'name' => $user->name,
                'email' => $tempEmail,
                'resetLink' => url('/reset-password/'.$token.'?email='.urlencode($request->new_email)),
                'matriculation_id' => $alumni->matric_number,
            ], function ($message) use ($request) {
                $message->to($request->new_email)
                    ->subject('Welcome to FuLafia Alumni Portal - Set Your Password');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email updated and password reset link sent successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating alumni email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the email. Please try again.',
            ]);
        }
    }

    public function resendCredentials(Request $request)
    {
        try {
            $request->validate([
                'matriculation_id' => 'required|string',
            ]);

            $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

            if (! $alumni) {
                return response()->json([
                    'success' => false,
                    'message' => 'No alumni found with this matriculation number.',
                ]);
            }

            if (! $this->canAccessExistingCredentials($alumni)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Onboarding and self-enrollment are currently closed. Please try again later.',
                ]);
            }

            $user = $alumni->user;
            $tempEmail = $this->selfEnrollment->tempEmailFromMatric($alumni->matric_number);

            $token = Password::createToken($user);

            Mail::send('emails.alumni-welcome', [
                'name' => $user->name,
                'email' => $tempEmail,
                'resetLink' => url('/reset-password/'.$token.'?email='.urlencode($user->email)),
                'matriculation_id' => $alumni->matric_number,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Welcome to FuLafia Alumni Portal - Set Your Password');
            });

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been resent successfully to '.$user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Error resending alumni credentials: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resending the credentials. Please try again.',
            ]);
        }
    }
}
