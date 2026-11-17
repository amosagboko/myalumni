<?php

namespace App\Imports;

use App\Models\Alumni;
use App\Models\LimitedAlumniRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class LimitedAlumniImport implements ToCollection, WithHeadingRow, WithChunkReading, WithEvents
{
    use Importable;

    /**
     * Relaxed vs AlumniImport: hyphenated segments such as 2021/SS/SOW-PT/0138 are allowed.
     */
    public const MATRIC_PATTERN = '/^(\d{10}|(\d{4}(?:\/[A-Z0-9\-]+)+\/[A-Z0-9\-]{3,12}))$/i';

    protected int $totalRows = 0;

    protected int $processedRows = 0;

    protected int $importedCount = 0;

    protected int $sourceRow = 1;

    /** @var list<array{row: int, firstname: string, surname: string, matric_number: string, reason: string}> */
    protected array $skippedRecords = [];

    /** @var array<string, true> */
    protected array $seenMatrics = [];

    protected string $importId;

    public function __construct()
    {
        $this->importId = uniqid('limited_import_');
    }

    public function collection(Collection $rows): void
    {
        $this->ingestRows($rows->all());
    }

    /**
     * Native CSV path — PhpSpreadsheet's getTotalRows()/xlsx parse is what blew the 30s limit.
     */
    public function importCsv(string $path): void
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to read the uploaded CSV file.');
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                return;
            }

            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            rewind($handle);

            $headers = fgetcsv($handle, 0, $delimiter);
            if ($headers === false) {
                return;
            }

            $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $headers);
            $chunk = [];
            $sourceRow = 1;

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $sourceRow++;

                if ($this->isEmptyCsvRow($data)) {
                    continue;
                }

                $row = ['_source_row' => $sourceRow];
                foreach ($headers as $index => $header) {
                    if ($header === '' || $header === '_source_row') {
                        continue;
                    }
                    $row[$header] = $data[$index] ?? null;
                }
                $chunk[] = $row;

                if (count($chunk) >= $this->chunkSize()) {
                    $this->ingestRows($chunk);
                    $chunk = [];
                }
            }

            if ($chunk !== []) {
                $this->ingestRows($chunk);
            }
        } finally {
            fclose($handle);
        }

        $this->markCompleted();
    }

    /**
     * @param  iterable<int, array<string, mixed>|Collection>  $rows
     */
    public function ingestRows(iterable $rows): void
    {
        $pending = [];

        foreach ($rows as $row) {
            $row = $row instanceof Collection ? $row->toArray() : (array) $row;
            $sourceRow = (int) ($row['_source_row'] ?? 0);
            if ($sourceRow <= 0) {
                $this->sourceRow++;
                $sourceRow = $this->sourceRow;
            } else {
                $this->sourceRow = max($this->sourceRow, $sourceRow);
            }

            $firstname = $this->cell($row, ['firstname', 'first_name']);
            $surname = $this->cell($row, ['surname', 'last_name', 'lastname']);
            $matric = $this->cell($row, ['matriculation_id', 'matric_number', 'matriculation_number']);

            if ($firstname === '' && $surname === '' && $matric === '') {
                if ($this->hasNamedIdentityKeys($row)) {
                    continue;
                }

                [$firstname, $surname, $matric] = $this->positionalIdentity($row);
                if ($firstname === '' && $surname === '' && $matric === '') {
                    continue;
                }
            }

            $this->processedRows++;
            $this->totalRows = max($this->totalRows, $this->processedRows);

            if ($firstname === '' || $surname === '' || $matric === '') {
                $this->skip($sourceRow, $firstname, $surname, $matric, $this->missingIdentityReason($firstname, $surname, $matric));
                continue;
            }

            if (is_numeric($matric) && ! str_contains($matric, '/')) {
                $matric = str_pad($matric, 10, '0', STR_PAD_LEFT);
            }

            if (! preg_match(self::MATRIC_PATTERN, $matric)) {
                $this->skip($sourceRow, $firstname, $surname, $matric, "Invalid matriculation number '{$matric}'.");
                continue;
            }

            if (isset($this->seenMatrics[$matric])) {
                $this->skip($sourceRow, $firstname, $surname, $matric, 'Duplicate matriculation number in this file.');
                continue;
            }

            $this->seenMatrics[$matric] = true;
            $pending[] = [
                'source_row' => $sourceRow,
                'matric_number' => $matric,
                'firstname' => $firstname,
                'surname' => $surname,
            ];
        }

        $this->flushPending($pending);
        $this->touchProgress();
    }

    /**
     * @param  list<array{source_row: int, matric_number: string, firstname: string, surname: string}>  $pending
     */
    protected function flushPending(array $pending): void
    {
        if ($pending === []) {
            return;
        }

        $matrics = array_column($pending, 'matric_number');
        $inAlumni = Alumni::whereIn('matric_number', $matrics)->pluck('matric_number')->flip();
        $inLimited = LimitedAlumniRecord::whereIn('matric_number', $matrics)->pluck('matric_number')->flip();

        $now = now();
        $createdBy = Auth::id();
        $insert = [];

        foreach ($pending as $row) {
            if (isset($inAlumni[$row['matric_number']])) {
                $this->skip(
                    $row['source_row'],
                    $row['firstname'],
                    $row['surname'],
                    $row['matric_number'],
                    'Matriculation number already exists in alumni.'
                );
                continue;
            }

            if (isset($inLimited[$row['matric_number']])) {
                $this->skip(
                    $row['source_row'],
                    $row['firstname'],
                    $row['surname'],
                    $row['matric_number'],
                    'Matriculation number already uploaded as a limited record.'
                );
                continue;
            }

            $insert[] = $row;
            $inLimited[$row['matric_number']] = $row['matric_number'];
        }

        foreach (array_chunk($insert, 200) as $chunk) {
            $payload = array_map(function (array $row) use ($now, $createdBy) {
                return [
                    'matric_number' => $row['matric_number'],
                    'firstname' => $row['firstname'],
                    'surname' => $row['surname'],
                    'year_of_graduation' => LimitedAlumniRecord::DEFAULT_GRADUATION_YEAR,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $chunk);

            try {
                LimitedAlumniRecord::insert($payload);
                $this->importedCount += count($payload);
            } catch (\Throwable $e) {
                Log::error('Limited alumni import batch failed, falling back to row inserts', [
                    'error' => $e->getMessage(),
                ]);
                $this->insertOneByOne($chunk, $payload);
            }
        }
    }

    /**
     * @param  list<array{source_row: int, matric_number: string, firstname: string, surname: string}>  $original
     * @param  list<array<string, mixed>>  $payload
     */
    protected function insertOneByOne(array $original, array $payload): void
    {
        foreach ($payload as $index => $row) {
            try {
                LimitedAlumniRecord::insert([$row]);
                $this->importedCount++;
            } catch (\Throwable $e) {
                $meta = $original[$index] ?? [];
                $this->skip(
                    (int) ($meta['source_row'] ?? 0),
                    (string) ($meta['firstname'] ?? ''),
                    (string) ($meta['surname'] ?? ''),
                    (string) ($row['matric_number'] ?? ''),
                    'Could not save this row: '.$e->getMessage()
                );
                Log::warning('Limited alumni import skipped row after batch failure', [
                    'matric' => $row['matric_number'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    protected function cell(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                continue;
            }

            $value = $this->normalizeCell($row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    protected function normalizeCell(mixed $value): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        $value = trim((string) $value);
        $value = preg_replace('/[\x{00A0}\x{1680}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u', '', $value) ?? $value;
        $value = trim($value);

        if (in_array(strtolower($value), ['', '-', '--', 'n/a', 'na', 'none', 'null', '#n/a', '#value!', '#ref!', '0'], true)) {
            return '';
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function hasNamedIdentityKeys(array $row): bool
    {
        foreach (['firstname', 'first_name', 'surname', 'last_name', 'lastname', 'matriculation_id', 'matric_number', 'matriculation_number'] as $key) {
            if (array_key_exists($key, $row)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $row
     * @return array{0: string, 1: string, 2: string}
     */
    protected function positionalIdentity(array $row): array
    {
        $values = [];
        foreach ($row as $key => $value) {
            if ($key === '_source_row') {
                continue;
            }
            $normalized = $this->normalizeCell($value);
            if ($normalized !== '') {
                $values[] = $normalized;
            }
            if (count($values) === 3) {
                break;
            }
        }

        return [$values[0] ?? '', $values[1] ?? '', $values[2] ?? ''];
    }

    protected function missingIdentityReason(string $firstname, string $surname, string $matric): string
    {
        $missing = [];
        if ($firstname === '') {
            $missing[] = 'first name';
        }
        if ($surname === '') {
            $missing[] = 'surname';
        }
        if ($matric === '') {
            $missing[] = 'matriculation number';
        }

        return 'Missing '.implode(', ', $missing).'.';
    }

    protected function skip(int $row, string $firstname, string $surname, string $matric, string $reason): void
    {
        $this->skippedRecords[] = [
            'row' => $row,
            'firstname' => $firstname,
            'surname' => $surname,
            'matric_number' => $matric,
            'reason' => $reason,
        ];
    }

    protected function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower(trim($header));

        return str_replace([' ', '-'], '_', $header);
    }

    /**
     * @param  list<string|null>  $data
     */
    protected function isEmptyCsvRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($this->normalizeCell($value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $this->markCompleted();
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    protected function touchProgress(): void
    {
        $progress = $this->processedRows > 0
            ? min(99, (int) round(($this->importedCount + count($this->skippedRecords)) / max($this->processedRows, 1) * 100))
            : 0;

        Cache::put("import_progress_{$this->importId}", [
            'progress' => $progress,
            'processed' => $this->processedRows,
            'total' => $this->totalRows,
            'errors' => array_column($this->skippedRecords, 'reason'),
            'completed' => false,
        ], now()->addHours(1));
    }

    public function markCompleted(): void
    {
        Cache::put("import_progress_{$this->importId}", [
            'progress' => 100,
            'processed' => $this->processedRows,
            'total' => $this->totalRows,
            'errors' => array_column($this->skippedRecords, 'reason'),
            'completed' => true,
        ], now()->addHours(1));

        Cache::put($this->skippedCacheKey(), $this->skippedRecords, now()->addHours(6));
    }

    public function skippedCacheKey(): string
    {
        return 'limited_import_skipped_'.$this->importId;
    }

    public function getImportId(): string
    {
        return $this->importId;
    }

    /** @return list<string> */
    public function getErrors(): array
    {
        return array_map(
            fn (array $row) => "Row {$row['row']}: {$row['reason']}",
            $this->skippedRecords
        );
    }

    /**
     * @return list<array{row: int, firstname: string, surname: string, matric_number: string, reason: string}>
     */
    public function getSkippedRecords(): array
    {
        return $this->skippedRecords;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return count($this->skippedRecords);
    }
}
