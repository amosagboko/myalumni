<?php

/**
 * FULAFIA student directory API — copy this file into another project.
 *
 * Lookup an undergraduate student by matriculation number.
 * This is a consumed API (the university directory). It does not log anyone in.
 *
 * -----------------------------------------------------------------------------
 * Endpoint
 * -----------------------------------------------------------------------------
 * POST https://api.fulafia.edu.ng/api/v1/request/student
 * Content-Type: application/json
 *
 * Headers:
 *   identity: studenthub
 *   Secret:   <FULAFIA_SECRET from the portal team — do not commit>
 *
 * Body:
 *   { "app": "UG", "query": "2024/BM/MBB/0019" }
 *
 * Success (use JSON flags, not HTTP status alone):
 *   { "status": 200, "success": true, "message": "", "data": [{
 *       "userId": "2024/BM/MBB/0019",
 *       "email": "student@example.com",
 *       "name": "Portal Student",
 *       "gsm": "08074040044",
 *       "department": "MEDICINE AND SURGERY"
 *   }] }
 *
 * Not found (HTTP may still be 200):
 *   { "status": 200, "success": false, "message": "Not found", "data": [] }
 *
 * Fields used by this support app from data[0]:
 *   userId = matric, email, name, gsm = phone, department
 *
 * -----------------------------------------------------------------------------
 * Environment
 * -----------------------------------------------------------------------------
 * FULAFIA_STUDENT_URL=https://api.fulafia.edu.ng/api/v1/request/student
 * FULAFIA_IDENTITY=studenthub
 * FULAFIA_SECRET=
 * FULAFIA_APP=UG
 * FULAFIA_TIMEOUT=60
 * FULAFIA_CONNECT_TIMEOUT=20
 * FULAFIA_HTTP_VERIFY_SSL=false
 *
 * TLS verify is off by default: this host often causes PHP cURL error 60.
 * Set FULAFIA_HTTP_VERIFY_SSL=true only if the environment trusts the cert chain.
 *
 * -----------------------------------------------------------------------------
 * PHP usage (this file)
 * -----------------------------------------------------------------------------
 *   $client = FulafiaStudentApi::fromEnv();
 *   $student = $client->fetchStudentByMatric('2024/BM/MBB/0019');
 *   // $student['userId'], $student['email'], $student['name'], $student['gsm'], $student['department']
 *
 * -----------------------------------------------------------------------------
 * cURL
 * -----------------------------------------------------------------------------
 *   curl -X POST "https://api.fulafia.edu.ng/api/v1/request/student" \
 *     -H "Content-Type: application/json" \
 *     -H "identity: studenthub" \
 *     -H "Secret: YOUR_FULAFIA_SECRET" \
 *     -d "{\"app\":\"UG\",\"query\":\"2024/BM/MBB/0019\"}"
 *
 * -----------------------------------------------------------------------------
 * JavaScript / Node
 * -----------------------------------------------------------------------------
 *   const res = await fetch('https://api.fulafia.edu.ng/api/v1/request/student', {
 *     method: 'POST',
 *     headers: {
 *       'Content-Type': 'application/json',
 *       identity: 'studenthub',
 *       Secret: process.env.FULAFIA_SECRET,
 *     },
 *     body: JSON.stringify({ app: 'UG', query: matric }),
 *   });
 *   const json = await res.json();
 *   if (!res.ok || json.success !== true || Number(json.status) !== 200 || !json.data?.[0]) {
 *     throw new Error(json.message || 'Student not found');
 *   }
 *   const student = json.data[0];
 */

class FulafiaStudentApiException extends RuntimeException
{
}

class FulafiaStudentApi
{
    public function __construct(
        private readonly string $url,
        private readonly string $secret,
        private readonly string $identity = 'studenthub',
        private readonly string $app = 'UG',
        private readonly int $timeoutSeconds = 60,
        private readonly int $connectTimeoutSeconds = 20,
        private readonly bool $verifySsl = false,
    ) {
    }

    /**
     * Build a client from environment variables (or the defaults used by this app).
     */
    public static function fromEnv(): self
    {
        $secret = (string) (getenv('FULAFIA_SECRET') ?: ($_ENV['FULAFIA_SECRET'] ?? ''));

        return new self(
            url: (string) (getenv('FULAFIA_STUDENT_URL') ?: ($_ENV['FULAFIA_STUDENT_URL'] ?? 'https://api.fulafia.edu.ng/api/v1/request/student')),
            secret: $secret,
            identity: (string) (getenv('FULAFIA_IDENTITY') ?: ($_ENV['FULAFIA_IDENTITY'] ?? 'studenthub')),
            app: (string) (getenv('FULAFIA_APP') ?: ($_ENV['FULAFIA_APP'] ?? 'UG')),
            timeoutSeconds: max(5, (int) (getenv('FULAFIA_TIMEOUT') ?: ($_ENV['FULAFIA_TIMEOUT'] ?? 60))),
            connectTimeoutSeconds: max(3, (int) (getenv('FULAFIA_CONNECT_TIMEOUT') ?: ($_ENV['FULAFIA_CONNECT_TIMEOUT'] ?? 20))),
            verifySsl: self::envFlag('FULAFIA_HTTP_VERIFY_SSL', false),
        );
    }

    /**
     * @return array<string, mixed> One student row from data[0]
     *
     * @throws FulafiaStudentApiException
     */
    public function fetchStudentByMatric(string $matric): array
    {
        $matric = trim($matric);
        if ($matric === '') {
            throw new FulafiaStudentApiException('Enter a matriculation number.');
        }

        if ($this->secret === '') {
            throw new FulafiaStudentApiException('FULAFIA_SECRET is not set.');
        }

        $payload = json_encode([
            'app' => $this->app,
            'query' => $matric,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($this->url);
        if ($ch === false) {
            throw new FulafiaStudentApiException('Could not start the student directory request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'identity: '.$this->identity,
                'Secret: '.$this->secret,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $errno !== 0) {
            throw new FulafiaStudentApiException(
                $this->isLikelyConnectionTimeout($error)
                    ? 'The student directory did not answer in time or could not be reached.'
                    : 'Could not reach the student directory. Please try again later.'
            );
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new FulafiaStudentApiException('The student directory did not respond. Please try again later.');
        }

        $json = json_decode((string) $body, true);
        if (! is_array($json)) {
            throw new FulafiaStudentApiException('Invalid response from the student directory.');
        }

        if (($json['success'] ?? false) !== true || (int) ($json['status'] ?? 0) !== 200) {
            $message = trim((string) ($json['message'] ?? ''));

            throw new FulafiaStudentApiException(
                $message !== '' ? $message : 'No student record was found for that matriculation number.'
            );
        }

        $data = $json['data'] ?? null;
        if (! is_array($data) || $data === []) {
            throw new FulafiaStudentApiException('No student record was found for that matriculation number.');
        }

        $row = $data[0] ?? null;
        if (! is_array($row)) {
            throw new FulafiaStudentApiException('Invalid student data from the directory.');
        }

        return $row;
    }

    protected function isLikelyConnectionTimeout(string $message): bool
    {
        return str_contains($message, 'cURL error 28')
            || str_contains($message, 'Failed to connect')
            || str_contains($message, 'Timeout was reached')
            || str_contains($message, 'Connection timed out');
    }

    protected static function envFlag(string $key, bool $default): bool
    {
        $raw = getenv($key);
        if ($raw === false) {
            $raw = $_ENV[$key] ?? null;
        }

        if ($raw === null || $raw === '') {
            return $default;
        }

        return in_array(strtolower((string) $raw), ['true', '1', 'yes', 'on'], true);
    }
}
