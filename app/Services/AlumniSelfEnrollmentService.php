<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\User;
use App\Support\NigeriaLocations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class AlumniSelfEnrollmentService
{
    public function __construct(
        protected FulafiaStudentApi $studentApi
    ) {}

    /**
     * Look up a student in the FULAFIA directory for self-enrollment.
     *
     * @return array<string, mixed>
     */
    public function lookupByMatric(string $matric): array
    {
        return $this->studentApi->fetchStudentByMatric($matric);
    }

    /**
     * Map a FULAFIA directory row into a self-enrollment pending payload.
     *
     * @param  array<string, mixed>  $student
     * @return array{
     *     matric_number: string,
     *     name: string,
     *     department: string,
     *     programme: string,
     *     faculty: string,
     *     gender: string,
     *     state: string,
     *     lga: string,
     *     phone_number: string,
     *     student_email: string,
     *     year_of_entry: int|null,
     *     year_of_graduation: int,
     *     category_label: string
     * }
     */
    public function buildPendingFromDirectory(array $student, string $fallbackMatric): array
    {
        $matric = trim((string) ($student['userId'] ?? $fallbackMatric));
        $state = NigeriaLocations::matchState((string) ($student['state'] ?? ''));
        $lga = NigeriaLocations::matchLga($state, (string) ($student['lga'] ?? ''));

        return [
            'matric_number' => $matric,
            'name' => trim((string) ($student['name'] ?? '')),
            'department' => trim((string) ($student['department'] ?? '')),
            'programme' => trim((string) ($student['programme'] ?? '')),
            'faculty' => trim((string) ($student['faculty'] ?? '')),
            'gender' => $this->normalizeGender((string) ($student['gender'] ?? '')),
            'state' => $state ?? '',
            'lga' => $lga ?? '',
            'phone_number' => trim((string) ($student['gsm'] ?? '')),
            'student_email' => trim((string) ($student['email'] ?? '')),
            'year_of_entry' => $this->guessYearOfEntry($matric),
            'year_of_graduation' => (int) config('fulafia.self_enrollment_graduation_year', date('Y')),
            'category_label' => 'Undergraduate (Full-time)',
        ];
    }

    /**
     * Whether pending directory data is complete enough to auto-create an account
     * without the confirm form. Date of birth is intentionally excluded.
     *
     * @param  array<string, mixed>  $pending
     */
    public function isPendingCompleteEnough(array $pending): bool
    {
        $required = [
            'matric_number',
            'name',
            'department',
            'programme',
            'faculty',
            'gender',
            'state',
            'lga',
            'year_of_entry',
        ];

        foreach ($required as $key) {
            $value = $pending[$key] ?? null;
            if ($value === null || $value === '' || $value === false) {
                return false;
            }
        }

        if (! in_array($pending['gender'], ['male', 'female'], true)) {
            return false;
        }

        if (! is_numeric($pending['year_of_entry'])) {
            return false;
        }

        return NigeriaLocations::isValidPair((string) $pending['state'], (string) $pending['lga']);
    }

    /**
     * Create User + Alumni for a confirmed 2026+ self-enrollment.
     *
     * @param  array{
     *     matric_number: string,
     *     name: string,
     *     department: string,
     *     programme: string,
     *     faculty: string,
     *     date_of_birth?: string|null,
     *     state: string,
     *     lga: string,
     *     year_of_entry: int,
     *     year_of_graduation?: int,
     *     gender: string,
     *     phone_number?: string|null,
     *     student_email?: string|null
     * }  $data
     */
    public function enroll(array $data): Alumni
    {
        $matric = trim($data['matric_number']);

        if (Alumni::where('matric_number', $matric)->exists()) {
            throw new RuntimeException('An alumni record already exists for this matriculation number.');
        }

        $category = AlumniCategory::where('slug', config('fulafia.self_enrollment_category_slug', 'undergraduate-full-time'))->first();

        if (! $category) {
            throw new RuntimeException('Default undergraduate full-time category is not configured.');
        }

        $alumniRole = Role::findByName('alumni');

        if (! $alumniRole) {
            throw new RuntimeException('Alumni role is not configured.');
        }

        $graduationYear = (int) ($data['year_of_graduation']
            ?? config('fulafia.self_enrollment_graduation_year', date('Y')));
        $tempEmail = $this->tempEmailFromMatric($matric);

        if (User::where('email', $tempEmail)->exists()) {
            throw new RuntimeException('A user account already exists for this matriculation number.');
        }

        return DB::transaction(function () use ($data, $matric, $category, $alumniRole, $graduationYear, $tempEmail) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => trim($data['name']),
                'email' => $tempEmail,
                'password' => Hash::make(Str::random(16)),
                'gender' => strtolower(trim($data['gender'])),
                'relationship' => 'single',
                'status' => 'active',
                'is_first_login' => true,
            ]);

            $user->assignRole($alumniRole);

            $alumni = Alumni::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'matric_number' => $matric,
                'programme' => trim($data['programme']),
                'department' => trim($data['department']),
                'faculty' => trim($data['faculty']),
                'year_of_graduation' => $graduationYear,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'state' => trim($data['state']),
                'lga' => trim($data['lga']),
                'year_of_entry' => (int) $data['year_of_entry'],
                'gender' => strtolower(trim($data['gender'])),
                'phone_number' => ! empty($data['phone_number']) ? trim($data['phone_number']) : null,
                'created_by' => null,
            ]);

            return $alumni->load(['user', 'category']);
        });
    }

    public function tempEmailFromMatric(string $matric): string
    {
        return strtolower(str_replace('/', '', $matric)).'@alumni.fulafia.edu.ng';
    }

    public function guessYearOfEntry(string $matric): ?int
    {
        if (preg_match('/^(\d{4})\//', trim($matric), $matches)) {
            $year = (int) $matches[1];

            if ($year >= 1990 && $year <= ((int) date('Y')) + 1) {
                return $year;
            }
        }

        return null;
    }

    public function normalizeGender(?string $value): string
    {
        $gender = strtolower(trim((string) $value));

        return in_array($gender, ['male', 'female'], true) ? $gender : '';
    }
}
