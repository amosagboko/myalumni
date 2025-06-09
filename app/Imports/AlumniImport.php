<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\Password;
use App\Jobs\SendAlumniWelcomeEmails;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class AlumniImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnError, WithEvents, WithCustomValueBinder
{
    use Importable, SkipsErrors;

    protected $totalRows = 0;
    protected $processedRows = 0;
    protected $errors = [];
    protected $importId;
    protected $invalidMatricIds = [];
    protected $skippedMatricIds = [];  // Add this to collect skipped matric IDs

    public function __construct()
    {
        $this->importId = uniqid('import_');
    }

    public function bindValue(Cell $cell, $value)
    {
        // Force matriculation_id column to be treated as string
        if ($cell->getColumn() === 'C') { // Assuming matriculation_id is in column C
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }

    public function model(array $row)
    {
        try {
            // Validate required fields
            if (empty($row['firstname']) || empty($row['surname']) || empty($row['matriculation_id'])) {
                throw new \Exception("Required fields are missing in row: " . json_encode($row));
            }

            // Convert matriculation_id to string and ensure proper formatting
            $matriculationId = trim((string) $row['matriculation_id']);
            $rowNumber = $this->processedRows + 1;
            
            // If it's a numeric value, pad with leading zeros to ensure 10 digits
            if (is_numeric($matriculationId)) {
                $matriculationId = str_pad($matriculationId, 10, '0', STR_PAD_LEFT);
            }
            
            // Validate matriculation ID format
            if (!preg_match('/^(\d{10}|(\d{4}(?:\/[A-Z]+)+\/[A-Z0-9]{4,6}))$/i', $matriculationId)) {
                // Collect the invalid matric ID with its details
                $this->invalidMatricIds[] = [
                    'row' => $rowNumber,
                    'matric_id' => $matriculationId,
                    'name' => trim($row['firstname']) . ' ' . trim($row['surname'])
                ];
                
                // If we have collected 5 examples, throw the error
                if (count($this->invalidMatricIds) >= 5) {
                    $examples = collect($this->invalidMatricIds)
                        ->map(function($item) {
                            return "Row {$item['row']}: '{$item['matric_id']}' (for {$item['name']})";
                        })
                        ->join("\n");
                    
                    throw new \Exception(
                        "Invalid matriculation ID format. Here are some examples:\n" . $examples . 
                        "\n\nAll matriculation IDs must be either:\n" .
                        "1. 10 digits (e.g., 1011700028)\n" .
                        "2. In format YYYY/DEPT/PROG/XXXX (e.g., 2018/BIO/HCP/0001)"
                    );
                }
                
                // Skip this row but continue processing others
                return null;
            }

            // Check if alumni with this matric number already exists
            $existingAlumni = Alumni::where('matric_number', $matriculationId)->first();
            if ($existingAlumni) {
                // Collect the skipped matric ID with its details
                $this->skippedMatricIds[] = [
                    'row' => $rowNumber,
                    'matric_id' => $matriculationId,
                    'name' => trim($row['firstname']) . ' ' . trim($row['surname']),
                    'existing_name' => $existingAlumni->user->name
                ];
                
                // Skip this row but continue processing others
                return null;
            }

            // Create user account
            $user = new User();
            $user->uuid = Str::uuid();
            $user->name = trim($row['firstname']) . ' ' . trim($row['surname']);
            $emailMatric = strtolower(str_replace('/', '', $matriculationId));
            $user->email = $emailMatric . '@alumni.fulafia.edu.ng';
            $user->password = Hash::make(Str::random(12));
            $user->gender = trim($row['gender']);
            $user->status = 'active';
            $user->created_by = Auth::id();
            $user->save();

            // Assign alumni role
            $alumniRole = Role::findByName('alumni');
            $user->assignRole($alumniRole);

            // Get or create alumni category
            $category = AlumniCategory::where('name', trim($row['category']))->first();
            if (!$category) {
                $category = AlumniCategory::create([
                    'name' => trim($row['category']),
                    'status' => 'active'
                ]);
            }

            // Create alumni record
            $alumni = new Alumni();
            $alumni->user_id = $user->id;
            $alumni->category_id = $category ? $category->id : null;
            $alumni->matric_number = $matriculationId;  // Use the processed matriculation ID
            $alumni->programme = trim($row['programme']);
            $alumni->department = trim($row['department']);
            $alumni->faculty = trim($row['faculty']);
            $alumni->year_of_graduation = (int)$row['year_of_graduation'];
            $alumni->date_of_birth = $row['date_of_birth'];
            $alumni->state = trim($row['state']);
            $alumni->lga = trim($row['lga']);
            $alumni->year_of_entry = (int)$row['year_of_entry'];
            $alumni->gender = trim($row['gender']);
            $alumni->created_by = Auth::id();
            $alumni->save();

            $this->processedRows++;
            
            // Update progress every 50 records
            if ($this->processedRows % 50 === 0) {
                $this->updateProgress();
            }
            
            return $alumni;

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row {$this->processedRows}: " . $e->getMessage();
            throw $e;
        }
    }

    protected function updateProgress()
    {
        $progress = $this->getProgress();
        Cache::put("import_progress_{$this->importId}", [
            'progress' => $progress,
            'processed' => $this->processedRows,
            'total' => $this->totalRows,
            'errors' => $this->errors,
            'completed' => false
        ], now()->addHours(1));
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'matriculation_id' => [
                'required',
                'string',
                'max:255',
                'unique:alumni,matric_number',
                'regex:/^(\d{10}|(\d{4}(?:\/[A-Z]+)+\/[A-Z0-9]{4,6}))$/i'
            ],
            'programme' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'year_of_graduation' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|string|exists:alumni_categories,name',
            'date_of_birth' => 'required|date',
            'state' => 'required|string|max:255',
            'lga' => 'required|string|max:255',
            'year_of_entry' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'gender' => 'required|string|max:50',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'firstname.required' => 'The first name field is required.',
            'surname.required' => 'The surname field is required.',
            'matriculation_id.required' => 'The matriculation ID field is required.',
            'matriculation_id.unique' => 'This matriculation ID has already been registered.',
            'matriculation_id.regex' => 'The matriculation ID must be one of the following formats: 10 digits (e.g., 1011700028), YYYY/DEPT/PROG/XXXX (e.g., 2018/BIO/HCP/0001), YYYY/DEPT/DEPT/XXXXXX (e.g., 2020/SC/BCH/0155DE), or YYYY/PGD/PG/SC/CSC/XXXX (e.g., 2021/PGD/PG/SC/CSC/0011)',
            'programme.required' => 'The programme field is required.',
            'department.required' => 'The department field is required.',
            'faculty.required' => 'The faculty field is required.',
            'year_of_graduation.required' => 'The year of graduation field is required.',
            'year_of_graduation.integer' => 'The year of graduation must be a valid year.',
            'year_of_graduation.min' => 'The year of graduation must be after 1900.',
            'year_of_graduation.max' => 'The year of graduation cannot be in the future.',
            'category.required' => 'The category field is required.',
            'category.exists' => 'The selected category is invalid.',
            'date_of_birth.required' => 'The date of birth field is required.',
            'date_of_birth.date' => 'The date of birth must be a valid date.',
            'state.required' => 'The state field is required.',
            'lga.required' => 'The LGA field is required.',
            'year_of_entry.required' => 'The year of entry field is required.',
            'year_of_entry.integer' => 'The year of entry must be a valid year.',
            'year_of_entry.min' => 'The year of entry must be after 1900.',
            'year_of_entry.max' => 'The year of entry cannot be in the future.',
            'gender.required' => 'The gender field is required.',
            'gender.string' => 'The gender must be a text value.',
            'gender.max' => 'The gender value cannot exceed 50 characters.',
        ];
    }

    public function batchSize(): int
    {
        return 500; // Keep efficient batch size for database operations
    }

    public function chunkSize(): int
    {
        return 500; // Keep efficient chunk size for database operations
    }

    public function onError(\Throwable $e)
    {
        Log::error('Alumni Import Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                $this->totalRows = $event->getReader()->getTotalRows()['Worksheet'] ?? 0;
                $this->processedRows = 0;
                $this->errors = [];
                $this->invalidMatricIds = [];
                $this->skippedMatricIds = [];
                
                // Initialize progress tracking
                Cache::put("import_progress_{$this->importId}", [
                    'progress' => 0,
                    'processed' => 0,
                    'total' => $this->totalRows,
                    'errors' => [],
                    'completed' => false
                ], now()->addHours(1));
            },
            AfterImport::class => function(AfterImport $event) {
                // Calculate actual total rows processed
                $totalProcessed = $this->processedRows + count($this->skippedMatricIds) + count($this->invalidMatricIds);
                
                Log::info("Import completed. Processed {$totalProcessed} of {$this->totalRows} rows.");

                // Log skipped matric IDs
                if (!empty($this->skippedMatricIds)) {
                    $skipped = collect($this->skippedMatricIds)
                        ->map(function($item) {
                            return "Row {$item['row']}: '{$item['matric_id']}' (for {$item['name']}) - Already exists as {$item['existing_name']}";
                        })
                        ->join("\n");
                    
                    Log::info("Skipped existing matriculation IDs:", ['skipped' => $skipped]);
                }

                // Log invalid matric IDs
                if (!empty($this->invalidMatricIds)) {
                    $examples = collect($this->invalidMatricIds)
                        ->map(function($item) {
                            return "Row {$item['row']}: '{$item['matric_id']}' (for {$item['name']})";
                        })
                        ->join("\n");
                    
                    Log::error("Invalid matriculation IDs found:", ['examples' => $examples]);
                }

                // Update final progress with summary
                $summary = [
                    'total_rows' => $totalProcessed,  // Use actual total processed
                    'processed' => $this->processedRows,
                    'skipped' => count($this->skippedMatricIds),
                    'invalid' => count($this->invalidMatricIds),
                    'successful' => $this->processedRows
                ];

                Cache::put("import_progress_{$this->importId}", [
                    'progress' => 100,
                    'processed' => $this->processedRows,
                    'total' => $totalProcessed,  // Use actual total processed
                    'errors' => $this->errors,
                    'skipped' => $this->skippedMatricIds,
                    'invalid' => $this->invalidMatricIds,
                    'summary' => $summary,
                    'completed' => true
                ], now()->addHours(1));

                // If we have skipped records, throw a warning
                if (!empty($this->skippedMatricIds)) {
                    $skippedExamples = collect($this->skippedMatricIds)
                        ->take(5)
                        ->map(function($item) {
                            return "Row {$item['row']}: '{$item['matric_id']}' (for {$item['name']}) - Already exists as {$item['existing_name']}";
                        })
                        ->join("\n");
                    
                    throw new \Exception(
                        "Import completed with some skipped records. {$summary['skipped']} records were skipped because they already exist.\n\n" .
                        "Here are some examples of skipped records:\n" . $skippedExamples . "\n\n" .
                        "Summary:\n" .
                        "- Total rows processed: {$summary['total_rows']}\n" .
                        "- Successfully imported: {$summary['successful']}\n" .
                        "- Skipped (already exist): {$summary['skipped']}\n" .
                        "- Invalid format: {$summary['invalid']}"
                    );
                }
            },
        ];
    }

    public function getImportId()
    {
        return $this->importId;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getProgress(): int
    {
        if ($this->totalRows === 0) {
            return 0;
        }
        return (int)(($this->processedRows / $this->totalRows) * 100);
    }
} 