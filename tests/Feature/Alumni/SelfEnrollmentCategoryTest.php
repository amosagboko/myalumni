<?php

namespace Tests\Feature\Alumni;

use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\OnboardingSetting;
use App\Services\AlumniDuesService;
use App\Services\AlumniSelfEnrollmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelfEnrollmentCategoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('alumni');
        $this->seedUndergraduateCategories();
    }

    public function test_part_time_directory_sample_maps_to_undergraduate_part_time(): void
    {
        $service = app(AlumniSelfEnrollmentService::class);
        $pending = $service->buildPendingFromDirectory($this->partTimeDirectoryRow(), '2021/SS/SOW-PT/0138');

        $this->assertSame('undergraduate-part-time', $pending['category_slug']);
        $this->assertSame('Undergraduate (Part-time)', $pending['category_label']);
        $this->assertTrue($service->isPendingCompleteEnough($pending));
    }

    public function test_unknown_or_full_time_directory_fields_default_to_full_time(): void
    {
        $service = app(AlumniSelfEnrollmentService::class);

        $unknown = $service->buildPendingFromDirectory([
            'userId' => '2021/SS/CSC/0001',
            'name' => 'Jane Full Time',
            'department' => 'Computer Science',
            'programme' => 'B.Sc Computer Science',
            'faculty' => 'Science',
            'gender' => 'Female',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'studyMode' => 'REGULAR',
            'programmeType' => 'Undergraduate',
        ], '2021/SS/CSC/0001');

        $this->assertSame('undergraduate-full-time', $unknown['category_slug']);
        $this->assertSame('Undergraduate (Full-time)', $unknown['category_label']);

        $explicitFullTime = $service->resolveUndergraduateCategory([
            'studyMode' => 'FULL_TIME',
            'programmeType' => 'Full Time',
            'entryMode' => 'UTME',
        ]);

        $this->assertSame('undergraduate-full-time', $explicitFullTime['slug']);
    }

    public function test_category_field_alone_can_mark_part_time(): void
    {
        $resolved = app(AlumniSelfEnrollmentService::class)->resolveUndergraduateCategory([
            'studyMode' => 'REGULAR',
            'category' => 'part-time',
        ]);

        $this->assertSame('undergraduate-part-time', $resolved['slug']);
    }

    public function test_enroll_assigns_part_time_category_from_pending_slug(): void
    {
        $service = app(AlumniSelfEnrollmentService::class);
        $pending = $service->buildPendingFromDirectory(
            $this->partTimeDirectoryRow('2021/SS/SOW-PT/'.uniqid()),
            'unused'
        );

        $alumni = $service->enroll([
            'matric_number' => $pending['matric_number'],
            'name' => $pending['name'],
            'department' => $pending['department'],
            'programme' => $pending['programme'],
            'faculty' => $pending['faculty'],
            'state' => $pending['state'],
            'lga' => $pending['lga'],
            'year_of_entry' => (int) $pending['year_of_entry'],
            'year_of_graduation' => (int) $pending['year_of_graduation'],
            'gender' => $pending['gender'],
            'phone_number' => $pending['phone_number'],
            'category_slug' => $pending['category_slug'],
        ]);

        $this->assertSame('undergraduate-part-time', $alumni->category->slug);
        $this->assertSame('003486JRC2PE', app(AlumniDuesService::class)->combinedServiceCodeFor($alumni));
    }

    public function test_enroll_defaults_to_full_time_when_slug_is_missing_or_invalid(): void
    {
        $service = app(AlumniSelfEnrollmentService::class);
        $base = [
            'matric_number' => '2021/SS/CSC/'.uniqid(),
            'name' => 'Jane Full Time',
            'department' => 'Computer Science',
            'programme' => 'B.Sc Computer Science',
            'faculty' => 'Science',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'year_of_graduation' => (int) date('Y'),
            'gender' => 'female',
        ];

        $withoutSlug = $service->enroll($base);
        $this->assertSame('undergraduate-full-time', $withoutSlug->category->slug);

        $tampered = $service->enroll(array_merge($base, [
            'matric_number' => '2021/SS/CSC/'.uniqid(),
            'category_slug' => 'postgraduate',
        ]));
        $this->assertSame('undergraduate-full-time', $tampered->category->slug);
    }

    public function test_directory_search_auto_enrolls_part_time_graduate(): void
    {
        $this->enableSelfEnrollment();
        config(['fulafia.secret' => 'test-secret']);

        $matric = '2021/SS/SOW-PT/'.substr(uniqid(), -6);
        $row = $this->partTimeDirectoryRow($matric);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'status' => 200,
                'message' => 'OK',
                'data' => [$row],
            ], 200),
        ]);

        $this->get(route('landing.search-credentials', ['matriculation_id' => $matric]))
            ->assertOk()
            ->assertSee('Undergraduate (Part-time)')
            ->assertSee($matric);

        $this->assertDatabaseHas('alumni', [
            'matric_number' => $matric,
            'category_id' => AlumniCategory::where('slug', 'undergraduate-part-time')->value('id'),
        ]);
    }

    public function test_confirm_form_uses_session_category_not_request_input(): void
    {
        $this->enableSelfEnrollment();

        $matric = '2021/SS/SOW-PT/'.substr(uniqid(), -6);
        $pending = app(AlumniSelfEnrollmentService::class)->buildPendingFromDirectory(
            array_merge($this->partTimeDirectoryRow($matric), [
                'gender' => '',
            ]),
            $matric
        );

        $this->assertFalse(app(AlumniSelfEnrollmentService::class)->isPendingCompleteEnough($pending));
        $this->assertSame('undergraduate-part-time', $pending['category_slug']);

        $this->withSession(['self_enroll_pending' => $pending])
            ->post(route('landing.self-enroll.submit'), [
                'programme' => $pending['programme'],
                'faculty' => $pending['faculty'],
                'state' => 'Nasarawa',
                'lga' => 'Obi',
                'gender' => 'male',
                'category_slug' => 'undergraduate-full-time',
            ])
            ->assertOk()
            ->assertSee('Undergraduate (Part-time)');

        $this->assertSame(
            'undergraduate-part-time',
            Alumni::where('matric_number', $matric)->first()?->category->slug
        );
    }

    public function test_part_time_combined_uses_issued_credo_product_and_keeps_separate_item_codes(): void
    {
        $codes = config('services.credocentral.service_codes');

        $this->assertSame('003486JRC2PE', $codes['combined']['undergraduate-part-time'] ?? null);
        $this->assertSame('003486O4WAZK', $codes['combined']['undergraduate-full-time']);
        $this->assertSame('0034860PZGM7', $codes['registration']['undergraduate-part-time']);
        $this->assertSame('003486M0T2MO', $codes['development_levy']['undergraduate-part-time']);
        $this->assertSame('003486MJKMC1', $codes['data_processing']['undergraduate-part-time']);
        $this->assertSame('0034862114FZ', $codes['tech_support']['undergraduate-part-time']);
        $this->assertSame('UG', config('fulafia.app'));
    }

    /**
     * @return array<string, mixed>
     */
    private function partTimeDirectoryRow(string $matric = '2021/SS/SOW-PT/0138'): array
    {
        return [
            'userId' => $matric,
            'name' => 'John Part Time',
            'department' => 'SOCIAL WORK',
            'programme' => 'B.Sc. Social Work',
            'faculty' => 'SOCIAL SCIENCES',
            'gender' => 'Male',
            'state' => 'Nasarawa',
            'lga' => 'Obi',
            'gsm' => '08012345678',
            'email' => 'student@example.com',
            'studyMode' => 'PART_TIME',
            'programmeType' => 'Part Time',
            'entryMode' => 'Part Time 1 (UTME)',
        ];
    }

    private function seedUndergraduateCategories(): void
    {
        AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            [
                'name' => 'Undergraduate (Full-time)',
                'description' => 'Undergraduate full-time alumni',
                'is_active' => true,
            ]
        );

        AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-part-time'],
            [
                'name' => 'Undergraduate (Part-time)',
                'description' => 'Undergraduate part-time alumni',
                'is_active' => true,
            ]
        );
    }

    private function enableSelfEnrollment(): void
    {
        OnboardingSetting::unguarded(function () {
            OnboardingSetting::query()->updateOrCreate(
                ['id' => 1],
                [
                    'is_onboarding_enabled' => true,
                    'is_self_enrollment_enabled' => true,
                    'is_student_affairs_clearance_enabled' => true,
                    'is_academic_affairs_clearance_enabled' => true,
                ]
            );
        });
    }
}
