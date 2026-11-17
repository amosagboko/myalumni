<?php

namespace Tests\Feature\Alumni;

use App\Imports\AlumniImport;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\FeeTemplate;
use App\Models\FeeType;
use App\Models\LimitedAlumniRecord;
use App\Models\OnboardingSetting;
use App\Models\User;
use App\Services\AlumniDuesService;
use App\Support\AcademicStructure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LimitedAlumniUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
        Role::findOrCreate('administrator');
        $this->seedUndergraduateCategories();
    }

    public function test_limited_import_stores_staging_rows_and_accepts_hyphenated_matrics(): void
    {
        $matric = '2021/SS/SOW-PT/'.$this->uniqueSegment();
        $file = $this->csvFile("firstname,surname,matriculation_id\nAda,Obi,{$matric}\n");

        $this->actingAs($this->admin())
            ->post(route('upload.alumni.limited'), ['file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('limited_alumni_records', [
            'matric_number' => $matric,
            'firstname' => 'Ada',
            'surname' => 'Obi',
            'year_of_graduation' => 2026,
        ]);
        $this->assertNull(LimitedAlumniRecord::where('matric_number', $matric)->value('completed_at'));
        $this->assertDatabaseMissing('alumni', ['matric_number' => $matric]);
        $this->assertDatabaseMissing('users', ['name' => 'Ada Obi']);
    }

    public function test_limited_import_accepts_header_aliases(): void
    {
        $matric = '2021/SS/LT-PT/'.$this->uniqueSegment();
        $file = $this->csvFile("first_name,surname,matric_number\nChika,Eze,{$matric}\n");

        $this->actingAs($this->admin())
            ->post(route('upload.alumni.limited'), ['file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('limited_alumni_records', [
            'matric_number' => $matric,
            'firstname' => 'Chika',
            'surname' => 'Eze',
        ]);
    }

    public function test_limited_import_skips_matrics_already_in_alumni_or_staging(): void
    {
        $existingAlumniMatric = '2021/SS/DUP-PT/'.$this->uniqueSegment();
        $existingLimitedMatric = '2021/SS/LIM-PT/'.$this->uniqueSegment();

        $this->createCatalogAlumni($existingAlumniMatric, 'Science', 'Computer Science', 'B.Sc Computer Science');
        LimitedAlumniRecord::create([
            'matric_number' => $existingLimitedMatric,
            'firstname' => 'Already',
            'surname' => 'There',
            'year_of_graduation' => 2026,
        ]);

        $file = $this->csvFile(
            "firstname,surname,matriculation_id\n".
            "New,Person,{$existingAlumniMatric}\n".
            "Also,There,{$existingLimitedMatric}\n"
        );

        $this->actingAs($this->admin())
            ->post(route('upload.alumni.limited'), ['file' => $file])
            ->assertRedirect();

        $skipped = session('limited_import_skipped', []);
        $this->assertCount(2, $skipped);
        $this->assertSame('Matriculation number already exists in alumni.', $skipped[0]['reason']);
        $this->assertSame('Matriculation number already uploaded as a limited record.', $skipped[1]['reason']);

        $this->assertSame(1, LimitedAlumniRecord::where('matric_number', $existingLimitedMatric)->count());
        $this->assertDatabaseMissing('limited_alumni_records', ['matric_number' => $existingAlumniMatric]);
    }

    public function test_limited_import_ignores_trailing_blank_rows_but_flags_real_partial_rows(): void
    {
        $good = '2021/SS/OK-PT/'.$this->uniqueSegment();
        $nbsp = "\xC2\xA0";
        $file = $this->csvFile(
            "firstname,surname,matriculation_id\n".
            "Ada,Obi,{$good}\n".
            "{$nbsp},{$nbsp},\n".
            ",,\n".
            "-,-,-\n".
            "John,,\n"
        );

        $admin = $this->admin();
        $this->actingAs($admin)
            ->from(route('upload.alumni'))
            ->post(route('upload.alumni.limited'), ['file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('limited_alumni_records', [
            'matric_number' => $good,
            'firstname' => 'Ada',
        ]);
        $this->assertDatabaseMissing('limited_alumni_records', ['firstname' => 'John']);

        $skipped = session('limited_import_skipped', []);
        $importId = session('limited_import_id');
        $this->assertCount(1, $skipped);
        $this->assertSame('John', $skipped[0]['firstname']);
        $this->assertSame('Missing surname, matriculation number.', $skipped[0]['reason']);

        $this->actingAs($admin)
            ->get(route('upload.alumni'))
            ->assertOk()
            ->assertSee('Records not uploaded')
            ->assertSee('John')
            ->assertSee('Missing surname, matriculation number.');

        $csv = $this->actingAs($admin)
            ->get(route('upload.alumni.limited.skipped', ['importId' => $importId]))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('firstname,surname,matriculation_id,reason', $csv);
        $this->assertStringContainsString('John', $csv);
        $this->assertStringContainsString('Missing surname, matriculation number.', $csv);
    }

    public function test_limited_import_batches_a_large_csv_and_skips_in_file_duplicates(): void
    {
        $lines = ['firstname,surname,matriculation_id'];
        $first = '2021/SS/BIG-PT/'.$this->uniqueSegment();
        $lines[] = "Ada,Obi,{$first}";
        $lines[] = "Ada,Obi,{$first}";

        for ($i = 0; $i < 120; $i++) {
            $lines[] = 'Name'.$i.',Surname'.$i.',2021/SS/BIG-PT/'.$this->uniqueSegment();
        }

        $file = $this->csvFile(implode("\n", $lines)."\n");

        $this->actingAs($this->admin())
            ->post(route('upload.alumni.limited'), ['file' => $file])
            ->assertRedirect();

        $this->assertSame(121, LimitedAlumniRecord::where('matric_number', 'like', '2021/SS/BIG-PT/%')->count());
        $this->assertSame(1, LimitedAlumniRecord::where('matric_number', $first)->count());
        $this->assertSame(
            'Duplicate matriculation number in this file.',
            session('limited_import_skipped')[0]['reason'] ?? null
        );
    }

    public function test_full_csv_upload_is_unchanged_and_rejects_limited_files(): void
    {
        $matric = '2021/SS/FULL/'.$this->uniqueSegment();
        $file = $this->csvFile("firstname,surname,matriculation_id\nAda,Obi,{$matric}\n");

        $this->actingAs($this->admin())
            ->post(route('upload.alumni.store'), ['file' => $file]);

        $this->assertDatabaseMissing('alumni', ['matric_number' => $matric]);
        $this->assertDatabaseMissing('limited_alumni_records', ['matric_number' => $matric]);

        $rules = (new AlumniImport)->rules();
        $this->assertStringContainsString('required', (string) $rules['programme']);
        $this->assertStringContainsString('required', (string) $rules['faculty']);
        $this->assertTrue(
            collect($rules['matriculation_id'])->contains(fn ($rule) => is_string($rule) && str_contains($rule, 'regex:'))
        );
    }

    public function test_public_search_diverts_incomplete_limited_records_to_completion_form(): void
    {
        $this->enableOnboarding();
        $record = $this->createLimitedRecord();

        $this->get(route('landing.search-credentials', ['matriculation_id' => $record->matric_number]))
            ->assertRedirect(route('landing.complete-profile'));

        $this->get(route('landing.complete-profile'))
            ->assertOk()
            ->assertSee($record->firstname)
            ->assertSee($record->surname)
            ->assertSee($record->matric_number)
            ->assertSee('2026')
            ->assertSee('readonly', false)
            ->assertDontSee('name="firstname"')
            ->assertDontSee('name="surname"')
            ->assertDontSee('name="matriculation_id"')
            ->assertDontSee('name="year_of_graduation"')
            ->assertSee('Date of birth (YYYY-MM-DD)')
            ->assertSee('placeholder="YYYY-MM-DD"', false)
            ->assertSee('Undergraduate (Full-time)')
            ->assertSee('Undergraduate (Part-time)')
            ->assertDontSee('value="postgraduate"', false)
            ->assertDontSee('value="diploma"', false);
    }

    public function test_search_does_not_divert_when_onboarding_is_closed(): void
    {
        $this->setOnboarding(false, true);
        $record = $this->createLimitedRecord();

        $this->from(route('landing'))
            ->get(route('landing.search-credentials', ['matriculation_id' => $record->matric_number]))
            ->assertRedirect(route('landing'));

        $this->assertFlashedContains('Onboarding is currently closed');
    }

    public function test_completed_limited_record_uses_existing_credentials_path(): void
    {
        $this->enableOnboarding();
        $matric = '2021/SS/DONE/'.$this->uniqueSegment();
        $alumni = $this->createCatalogAlumni($matric, 'Science', 'Computer Science', 'B.Sc Computer Science');

        LimitedAlumniRecord::create([
            'matric_number' => $matric,
            'firstname' => 'Done',
            'surname' => 'Already',
            'year_of_graduation' => 2026,
            'completed_at' => now(),
        ]);

        $this->get(route('landing.search-credentials', ['matriculation_id' => $matric]))
            ->assertOk()
            ->assertSee($alumni->user->name)
            ->assertSee('Update Email');
    }

    public function test_completion_form_ignores_posted_identity_and_creates_alumni_then_credentials(): void
    {
        $this->enableOnboarding();
        $this->createCatalogAlumni(
            '2020/SC/CAT/'.$this->uniqueSegment(),
            'Social Sciences',
            'Social Work',
            'B.Sc. Social Work'
        );
        $this->seedOnboardingFeesFor2026();

        $record = $this->createLimitedRecord('Ngozi', 'Bello');
        $this->withSession(['limited_alumni_pending' => [
            'id' => $record->id,
            'matric_number' => $record->matric_number,
            'firstname' => $record->firstname,
            'surname' => $record->surname,
            'year_of_graduation' => 2026,
        ]])->post(route('landing.complete-profile.submit'), [
            'firstname' => 'Hacked',
            'surname' => 'Name',
            'matriculation_id' => 'CHANGED',
            'year_of_graduation' => 2010,
            'faculty' => 'Social Sciences',
            'department' => 'Social Work',
            'programme' => 'B.Sc. Social Work',
            'category_slug' => 'undergraduate-part-time',
            'date_of_birth' => '1999-05-12',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2018,
            'gender' => 'female',
        ])->assertOk()
            ->assertSee('Ngozi Bello')
            ->assertSee($record->matric_number)
            ->assertSee('Update Email');

        $alumni = Alumni::where('matric_number', $record->matric_number)->first();
        $this->assertNotNull($alumni);
        $this->assertSame('Ngozi Bello', $alumni->user->name);
        $this->assertSame(2026, (int) $alumni->year_of_graduation);
        $this->assertSame('undergraduate-part-time', $alumni->category->slug);
        $this->assertSame('female', $alumni->user->gender);
        $this->assertSame('1999-05-12', $alumni->date_of_birth?->format('Y-m-d'));
        $this->assertSame('Social Sciences', $alumni->faculty);
        $this->assertTrue($alumni->user->is_first_login);
        $this->assertTrue($alumni->user->hasRole('alumni'));
        $this->assertTrue(Hash::isHashed($alumni->user->password));
        $this->assertNotNull($record->fresh()->completed_at);

        $fees = app(AlumniDuesService::class)->getOnboardingFeeTemplates($alumni);
        $this->assertTrue($fees->isNotEmpty());
    }

    public function test_invalid_academic_combination_and_session_expiry_are_rejected(): void
    {
        $this->enableOnboarding();
        $this->createCatalogAlumni(
            '2020/SC/CAT/'.$this->uniqueSegment(),
            'Science',
            'Computer Science',
            'B.Sc Computer Science'
        );
        $record = $this->createLimitedRecord();

        $this->withSession(['limited_alumni_pending' => [
            'id' => $record->id,
            'matric_number' => $record->matric_number,
        ]])->from(route('landing.complete-profile'))
            ->post(route('landing.complete-profile.submit'), [
                'faculty' => 'Science',
                'department' => 'Computer Science',
                'programme' => 'Not A Real Programme',
                'category_slug' => 'undergraduate-full-time',
                'date_of_birth' => '1998-01-01',
                'state' => 'Nasarawa',
                'lga' => 'Lafia',
                'year_of_entry' => 2018,
                'gender' => 'male',
            ])->assertRedirect(route('landing.complete-profile'))
            ->assertSessionHasErrors('programme');

        $this->assertDatabaseMissing('alumni', ['matric_number' => $record->matric_number]);
    }

    public function test_completion_form_requires_an_active_search_session(): void
    {
        $this->enableOnboarding();

        $this->get(route('landing.complete-profile'))
            ->assertRedirect(route('landing'));

        $this->assertFlashedContains('session expired');
    }

    public function test_academic_structure_excludes_test_faculty_rows(): void
    {
        $this->createCatalogAlumni('2020/SC/OK/'.$this->uniqueSegment(), 'Science', 'Physics', 'B.Sc Physics');
        $this->createCatalogAlumni('2020/SC/TST/'.$this->uniqueSegment(), 'Test Faculty 9', 'Ghost Dept', 'Ghost Programme');

        $tree = AcademicStructure::tree();

        $this->assertArrayHasKey('Science', $tree);
        $this->assertArrayNotHasKey('Test Faculty 9', $tree);
        $this->assertContains('B.Sc Physics', $tree['Science']['Physics']);
    }

    public function test_academic_structure_collapses_duplicate_programme_labels(): void
    {
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'Science', 'Computer Science', 'B.Sc. Computer Science');
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'Science', 'Computer Science', 'B.Sc.  Computer Science');
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'Science', 'Computer Science', "B.Sc.\xC2\xA0Computer Science");
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'science', 'computer science', 'b.sc. computer science');
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'Science', 'Mathematics', 'B.Sc. Mathematics');
        $this->createCatalogAlumni('2020/SC/DUP/'.$this->uniqueSegment(), 'Science', 'Mathematics', 'B.Sc. Mathematics');

        $tree = AcademicStructure::tree();
        $science = $tree['Science'] ?? $tree['science'] ?? [];

        $this->assertCount(1, $science['Computer Science'] ?? $science['computer science'] ?? []);
        $this->assertCount(1, $science['Mathematics'] ?? []);
        $this->assertTrue(AcademicStructure::isValidCombination(
            'Science',
            'Computer Science',
            'B.Sc. Computer Science'
        ));
    }

    public function test_second_submit_is_rejected_after_completion(): void
    {
        $this->enableOnboarding();
        $this->createCatalogAlumni(
            '2020/SC/CAT/'.$this->uniqueSegment(),
            'Science',
            'Computer Science',
            'B.Sc Computer Science'
        );
        $record = $this->createLimitedRecord();
        $payload = [
            'faculty' => 'Science',
            'department' => 'Computer Science',
            'programme' => 'B.Sc Computer Science',
            'category_slug' => 'undergraduate-full-time',
            'date_of_birth' => '1998-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2018,
            'gender' => 'male',
        ];

        $this->withSession(['limited_alumni_pending' => [
            'id' => $record->id,
            'matric_number' => $record->matric_number,
        ]])->post(route('landing.complete-profile.submit'), $payload)
            ->assertOk();

        $this->withSession(['limited_alumni_pending' => [
            'id' => $record->id,
            'matric_number' => $record->matric_number,
        ]])->post(route('landing.complete-profile.submit'), $payload)
            ->assertRedirect(route('landing'));

        $this->assertFlashedContains('already been completed');

        $this->assertSame(1, Alumni::where('matric_number', $record->matric_number)->count());
    }

    private function csvFile(string $contents): UploadedFile
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'limited-'.uniqid('', true).'.csv';
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'limited.csv', 'text/csv', null, true);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('administrator');

        return $user;
    }

    private function createLimitedRecord(string $first = 'Ada', string $surname = 'Obi'): LimitedAlumniRecord
    {
        return LimitedAlumniRecord::create([
            'matric_number' => '2021/SS/SOW-PT/'.$this->uniqueSegment(),
            'firstname' => $first,
            'surname' => $surname,
            'year_of_graduation' => 2026,
        ]);
    }

    private function createCatalogAlumni(string $matric, string $faculty, string $department, string $programme): Alumni
    {
        $category = AlumniCategory::where('slug', 'undergraduate-full-time')->firstOrFail();
        $user = User::factory()->create();
        $user->assignRole('alumni');

        return Alumni::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'matric_number' => $matric,
            'programme' => $programme,
            'department' => $department,
            'faculty' => $faculty,
            'year_of_graduation' => 2025,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2018,
            'gender' => 'male',
        ]);
    }

    private function seedOnboardingFeesFor2026(): void
    {
        $partTime = AlumniCategory::where('slug', 'undergraduate-part-time')->firstOrFail();
        $fullTime = AlumniCategory::where('slug', 'undergraduate-full-time')->firstOrFail();
        $registration = FeeType::firstOrCreate(
            ['code' => 'registration'],
            ['name' => 'Registration', 'description' => 'Registration', 'is_active' => true, 'is_system' => true]
        );

        foreach ([$partTime, $fullTime] as $category) {
            FeeTemplate::updateOrCreate(
                [
                    'fee_type_id' => $registration->id,
                    'category_id' => $category->id,
                    'graduation_year' => 2026,
                    'fee_purpose' => FeeTemplate::PURPOSE_ONBOARDING,
                ],
                [
                    'amount' => 5000,
                    'description' => 'Registration '.$category->slug.' 2026',
                    'is_active' => true,
                    'valid_from' => now()->subDay(),
                    'valid_until' => '2026-12-31',
                ]
            );
        }
    }

    private function seedUndergraduateCategories(): void
    {
        AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate (Full-time)', 'description' => 'Undergraduate full-time alumni', 'is_active' => true]
        );
        AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-part-time'],
            ['name' => 'Undergraduate (Part-time)', 'description' => 'Undergraduate part-time alumni', 'is_active' => true]
        );
        AlumniCategory::firstOrCreate(
            ['slug' => 'postgraduate'],
            ['name' => 'Postgraduate', 'description' => 'Postgraduate alumni', 'is_active' => true]
        );
        AlumniCategory::firstOrCreate(
            ['slug' => 'diploma'],
            ['name' => 'Diploma', 'description' => 'Diploma alumni', 'is_active' => true]
        );
    }

    private function enableOnboarding(): void
    {
        $this->setOnboarding(true, false);
    }

    private function setOnboarding(bool $onboarding, bool $selfEnrollment): void
    {
        OnboardingSetting::unguarded(function () use ($onboarding, $selfEnrollment) {
            OnboardingSetting::query()->updateOrCreate(
                ['id' => 1],
                [
                    'is_onboarding_enabled' => $onboarding,
                    'is_self_enrollment_enabled' => $selfEnrollment,
                    'is_student_affairs_clearance_enabled' => true,
                    'is_academic_affairs_clearance_enabled' => true,
                ]
            );
        });
    }

    private function uniqueSegment(): string
    {
        return 'X'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 7));
    }

    private function assertFlashedContains(string $needle): void
    {
        $messages = collect(session('flasher::envelopes', []))
            ->map(function ($envelope) {
                return method_exists($envelope, 'getMessage') ? $envelope->getMessage() : '';
            })
            ->push((string) session('error'))
            ->push((string) session('success'))
            ->push((string) session('warning'))
            ->implode(' ');

        $this->assertStringContainsStringIgnoringCase($needle, $messages);
    }
}
