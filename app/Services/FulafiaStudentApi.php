<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FulafiaStudentApi
{
    public function __construct(
        protected ?string $url = null,
        protected ?string $secret = null,
        protected ?string $identity = null,
        protected ?string $app = null,
        protected ?int $timeoutSeconds = null,
        protected ?int $connectTimeoutSeconds = null,
        protected ?bool $verifySsl = null,
    ) {
        $this->url = $url ?? config('fulafia.student_url');
        $this->secret = trim((string) ($secret ?? config('fulafia.secret', '')));
        $this->identity = $identity ?? config('fulafia.identity', 'studenthub');
        $this->app = $app ?? config('fulafia.app', 'UG');
        $this->timeoutSeconds = $timeoutSeconds ?? (int) config('fulafia.timeout', 60);
        $this->connectTimeoutSeconds = $connectTimeoutSeconds ?? (int) config('fulafia.connect_timeout', 20);
        $this->verifySsl = $verifySsl ?? (bool) config('fulafia.verify_ssl', false);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchStudentByMatric(string $matric): array
    {
        $matric = trim($matric);

        if ($matric === '') {
            throw new RuntimeException('Enter a matriculation number.');
        }

        if ($this->secret === '') {
            throw new RuntimeException('FULAFIA_SECRET is not configured.');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'identity' => $this->identity,
                'Secret' => $this->secret,
            ])
                ->timeout($this->timeoutSeconds)
                ->connectTimeout($this->connectTimeoutSeconds)
                ->withOptions(['verify' => $this->verifySsl])
                ->post($this->url, [
                    'app' => $this->app,
                    'query' => $matric,
                ]);
        } catch (\Throwable $e) {
            Log::warning('FULAFIA student API request failed', [
                'matric' => $matric,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Could not reach the student directory. Please try again later.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('The student directory did not respond. Please try again later.');
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('Invalid response from the student directory.');
        }

        if (($json['success'] ?? false) !== true || (int) ($json['status'] ?? 0) !== 200) {
            $message = trim((string) ($json['message'] ?? ''));

            throw new RuntimeException(
                $message !== '' ? $message : 'No student record was found for that matriculation number.'
            );
        }

        $data = $json['data'] ?? null;

        if (! is_array($data) || $data === []) {
            throw new RuntimeException('No student record was found for that matriculation number.');
        }

        $row = $data[0] ?? null;

        if (! is_array($row) || empty($row['userId'])) {
            throw new RuntimeException('Invalid student data from the directory.');
        }

        return $row;
    }
}
