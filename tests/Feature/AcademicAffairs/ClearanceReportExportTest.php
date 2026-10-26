<?php

namespace Tests\Feature\AcademicAffairs;

use App\Livewire\AcademicAffairs\ClearanceReport as AcademicAffairsClearanceReport;
use App\Livewire\StudentAffairs\ClearanceReport as StudentAffairsClearanceReport;
use App\Models\Alumni;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class ClearanceReportExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('academic-affairs');
        Role::findOrCreate('student-affairs');
        Role::findOrCreate('alumni');
    }

    public function test_academic_affairs_report_page_shows_pdf_and_excel_buttons(): void
    {
        $officer = $this->academicAffairsUser();

        $this->actingAs($officer)
            ->get(route('academic-affairs.clearance-report'))
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('Download Excel');
    }

    public function test_student_affairs_report_page_does_not_show_export_buttons(): void
    {
        $officer = $this->studentAffairsUser();

        $this->actingAs($officer)
            ->get(route('student-affairs.clearance-report'))
            ->assertOk()
            ->assertDontSee('Download PDF')
            ->assertDontSee('Download Excel');
    }

    public function test_unauthorised_users_cannot_open_academic_affairs_report(): void
    {
        $alumni = User::factory()->create();
        $alumni->assignRole('alumni');

        $this->actingAs($alumni)
            ->get(route('academic-affairs.clearance-report'))
            ->assertForbidden();
    }

    public function test_excel_export_includes_only_filtered_records(): void
    {
        $officer = $this->academicAffairsUser();
        $matching = $this->createAlumniRecord('AA Export Match', 'MAT-AA-EXP-1', 2035, true);
        $this->createAlumniRecord('Other Graduate', 'MAT-AA-EXP-2', 2020, false);

        $response = Livewire::actingAs($officer)
            ->test(AcademicAffairsClearanceReport::class)
            ->set('year', '2035')
            ->set('status', 'cleared')
            ->instance()
            ->exportExcel();

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString(
            'academic-affairs-clearance-report-',
            (string) $response->headers->get('content-disposition')
        );
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $values = $sheet->toArray(null, true, true, true);
        $body = collect($values)->reject(fn ($row, $index) => (int) $index < 5);

        $this->assertTrue($body->contains(fn ($row) => str_contains((string) ($row['C'] ?? ''), $matching->matric_number)));
        $this->assertFalse($body->contains(fn ($row) => str_contains((string) ($row['C'] ?? ''), 'MAT-AA-EXP-2')));
        $this->assertTrue($body->contains(fn ($row) => ($row['D'] ?? null) === 'Cleared'));
        $this->assertStringContainsString('Graduation year: 2035', (string) $sheet->getCell('A3')->getValue());
        $this->assertStringContainsString('Status: Cleared', (string) $sheet->getCell('A3')->getValue());
    }

    public function test_pdf_export_includes_filtered_values_and_headings(): void
    {
        $officer = $this->academicAffairsUser();
        $matching = $this->createAlumniRecord('AA Pdf Match', 'MAT-AA-PDF-1', 2035, true);

        $response = Livewire::actingAs($officer)
            ->test(AcademicAffairsClearanceReport::class)
            ->set('search', 'MAT-AA-PDF-1')
            ->instance()
            ->exportPdf();

        $this->assertNotNull($response);
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString(
            'academic-affairs-clearance-report-',
            $response->headers->get('content-disposition')
        );

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF', $content);

        $html = view('exports.academic-affairs-clearance-report-pdf', [
            'rows' => collect([(object) [
                'cleared_at' => now(),
                'alumni_name' => $matching->user->name,
                'matric_number' => $matching->matric_number,
                'is_cleared' => true,
                'actor_name' => null,
                'year_of_graduation' => $matching->year_of_graduation,
            ]]),
            'filters' => [
                'Search' => 'MAT-AA-PDF-1',
                'Graduation year' => 'All years',
                'Status' => 'All statuses',
                'Actor' => 'All',
            ],
            'generatedAt' => now(),
            'reason' => 'To collect result statement',
        ])->render();

        $this->assertStringContainsString('Academic Affairs Clearance Report', $html);
        $this->assertStringContainsString($matching->matric_number, $html);
        $this->assertStringContainsString('Search: MAT-AA-PDF-1', $html);
        $this->assertStringContainsString('When', $html);
        $this->assertStringContainsString('To collect result statement', $html);
    }

    public function test_empty_filters_show_a_clear_message_instead_of_a_file(): void
    {
        $officer = $this->academicAffairsUser();
        $this->createAlumniRecord('Someone', 'MAT-AA-EMPTY-1', 2035, true);

        $component = Livewire::actingAs($officer)
            ->test(AcademicAffairsClearanceReport::class)
            ->set('search', 'no-such-alumni-xyz');

        $this->assertNull($component->instance()->exportExcel());
        $component->assertSet(
            'exportMessage',
            'No records match the current filters. Adjust your filters and try again.'
        );

        $this->assertNull($component->instance()->exportPdf());
        $component->assertSet(
            'exportMessage',
            'No records match the current filters. Adjust your filters and try again.'
        );
    }

    public function test_student_affairs_component_does_not_expose_export_actions(): void
    {
        $this->assertFalse(method_exists(StudentAffairsClearanceReport::class, 'exportExcel'));
        $this->assertFalse(method_exists(StudentAffairsClearanceReport::class, 'exportPdf'));
    }

    private function academicAffairsUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('academic-affairs');

        return $user;
    }

    private function studentAffairsUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('student-affairs');

        return $user;
    }

    private function createAlumniRecord(string $name, string $matric, int $year, bool $cleared): Alumni
    {
        $user = User::factory()->create(['name' => $name]);

        return Alumni::create([
            'user_id' => $user->id,
            'matric_number' => $matric,
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'faculty' => 'Science',
            'year_of_graduation' => $year,
            'date_of_birth' => '1995-01-01',
            'state' => 'Nasarawa',
            'lga' => 'Lafia',
            'year_of_entry' => 2016,
            'gender' => 'male',
            'academic_affairs_cleared' => $cleared,
        ]);
    }
}
