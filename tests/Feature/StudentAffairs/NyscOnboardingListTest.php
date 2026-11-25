<?php

namespace Tests\Feature\StudentAffairs;

use App\Livewire\StudentAffairs\NyscOnboarding;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class NyscOnboardingListTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('student-affairs');
        Role::findOrCreate('alumni');
    }

    public function test_list_shows_only_sa_cleared_alumni_and_excel_follows_the_faculty_filter(): void
    {
        $officer = $this->officer();
        $alpha = $this->alumnus('Alpha Person', 'NYSC-ALPHA', 'Alpha Faculty', 'Alpha Dept', 'Alpha Programme', 2091, true);
        $beta = $this->alumnus('Beta Person', 'NYSC-BETA', 'Beta Faculty', 'Beta Dept', 'Beta Programme', 2092, true);
        $pending = $this->alumnus('Pending Person', 'NYSC-PENDING', 'Alpha Faculty', 'Alpha Dept', 'Alpha Programme', 2091, false);

        $this->actingAs($officer)
            ->get(route('student-affairs.nysc-onboarding', ['faculty' => 'Alpha Faculty']))
            ->assertOk()
            ->assertSee('NYSC Onboarding')
            ->assertSee('Download Excel')
            ->assertSee($alpha->matric_number)
            ->assertSee('Alpha Programme')
            ->assertDontSee($beta->matric_number)
            ->assertDontSee($pending->matric_number)
            ->assertDontSee('Beta Dept');

        $response = Livewire::actingAs($officer)
            ->test(NyscOnboarding::class)
            ->set('faculty', 'Alpha Faculty')
            ->set('department', 'Alpha Dept')
            ->set('programme', 'Alpha Programme')
            ->set('year', '2091')
            ->assertSee($alpha->matric_number)
            ->assertDontSee($beta->matric_number)
            ->assertDontSee($pending->matric_number)
            ->instance()
            ->exportExcel();

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('nysc-onboarding-', $response->headers->get('content-disposition'));

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $values = $sheet->toArray(null, true, true, true);
        $body = collect($values)->reject(fn ($row, $index) => (int) $index < 6);

        $this->assertTrue($body->contains(fn ($row) => ($row['B'] ?? null) === $alpha->matric_number));
        $this->assertFalse($body->contains(fn ($row) => ($row['B'] ?? null) === $beta->matric_number));
        $this->assertFalse($body->contains(fn ($row) => ($row['B'] ?? null) === $pending->matric_number));
        $this->assertStringContainsString('Faculty: Alpha Faculty', (string) $sheet->getCell('A3')->getValue());
        $this->assertSame('Name', $sheet->getCell('A5')->getValue());
        $this->assertSame('Programme', $sheet->getCell('E5')->getValue());
    }

    public function test_empty_filter_does_not_download_a_file(): void
    {
        $this->alumnus('Alpha Person', 'NYSC-EMPTY', 'Alpha Faculty', 'Alpha Dept', 'Alpha Programme', 2091, true);

        $component = Livewire::actingAs($this->officer())
            ->test(NyscOnboarding::class)
            ->set('faculty', 'Missing Faculty');

        $this->assertNull($component->instance()->exportExcel());
        $component->assertSet(
            'exportMessage',
            'No records match the current filters. Adjust your filters and try again.'
        );
    }

    public function test_other_roles_cannot_open_nysc_onboarding(): void
    {
        $user = User::factory()->create();
        $user->assignRole('alumni');

        $this->actingAs($user)
            ->get(route('student-affairs.nysc-onboarding'))
            ->assertForbidden();
    }

    private function officer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('student-affairs');

        return $user;
    }

    private function alumnus(
        string $name,
        string $matric,
        string $faculty,
        string $department,
        string $programme,
        int $year,
        bool $cleared
    ): Alumni {
        $category = AlumniCategory::firstOrCreate(
            ['slug' => 'undergraduate-full-time'],
            ['name' => 'Undergraduate Full Time', 'description' => 'Undergraduate full time alumni', 'is_active' => true]
        );

        return Alumni::create([
            'user_id' => User::factory()->create(['name' => $name])->id,
            'category_id' => $category->id,
            'matric_number' => $matric,
            'programme' => $programme,
            'department' => $department,
            'faculty' => $faculty,
            'year_of_graduation' => $year,
            'date_of_birth' => '2000-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2021,
            'gender' => 'male',
            'contact_address' => '12 Alumni Street, Lafia',
            'phone_number' => '08012345678',
            'qualification_type' => 'B.Sc',
            'student_affairs_cleared' => $cleared,
        ]);
    }
}
