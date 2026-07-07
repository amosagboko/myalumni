<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class CredoCentralService
{
    protected $baseUrl;
    protected $publicKey;
    protected $secretKey;

    public function __construct(
        protected PaymentCompletionService $paymentCompletion
    ) {
        $this->baseUrl = rtrim(config('services.credocentral.base_url', 'https://api.credocentral.com'), '/');
        $this->publicKey = config('services.credocentral.public_key');
        $this->secretKey = config('services.credocentral.secret_key');

        // Log API configuration on service initialization
        Log::info('Credo Central Service initialized', [
            'base_url' => $this->baseUrl,
            'has_public_key' => !empty($this->publicKey),
            'has_secret_key' => !empty($this->secretKey),
            'environment' => app()->environment()
        ]);
    }

    /**
     * Get HTTP client with proper configuration
     */
    protected function getHttpClient()
    {
        $client = Http::withHeaders([
            'Authorization' => $this->publicKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
        ->timeout(30); // Set a 30-second timeout

        // Add retry logic with proper error handling
        $client->retry(3, 100, function ($exception) {
            Log::warning('Retrying Credo Central API request', [
                'error' => $exception->getMessage()
            ]);
            return $exception instanceof \Illuminate\Http\Client\ConnectionException;
        });

        return $client;
    }

    /**
     * HTTP client for server-side verification (requires secret key).
     */
    protected function getSecretHttpClient()
    {
        return Http::withHeaders([
            'Authorization' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout(30)
            ->retry(3, 100, function ($exception) {
                Log::warning('Retrying Credo Central verify request', [
                    'error' => $exception->getMessage(),
                ]);

                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            });
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(Transaction $transaction)
    {
        $endpoint = '/transaction/initialize';
        $fullUrl = $this->baseUrl . $endpoint;

        // Determine the service code based on the fee type code, category, and qualification type
        $feeTypeCode = $transaction->feeTemplate->feeType->code;
        $categorySlug = $transaction->feeTemplate->category->slug ?? null;
        $qualificationType = $transaction->alumni->qualification_type ?? null;
        
        // Get service codes for this fee type
        $feeTypeServiceCodes = config('services.credocentral.service_codes.' . $feeTypeCode);
        $serviceCode = null;

        // If it's a simple string (backward compatibility), use it directly
        if (is_string($feeTypeServiceCodes)) {
            $serviceCode = $feeTypeServiceCodes;
        } 
        // If it's an array (category-specific), look for category-specific code
        elseif (is_array($feeTypeServiceCodes)) {
            // For postgraduate category, check qualification type for subcategories (PhD, MSc, PGD)
            if ($categorySlug === 'postgraduate' && $qualificationType) {
                // Normalize qualification type (handle variations like "PhD", "Ph.D", "phd", etc.)
                $qualificationNormalized = strtolower(trim($qualificationType));
                // Remove dots and spaces, standardize
                $qualificationNormalized = str_replace(['.', ' ', '_'], '', $qualificationNormalized);
                
                // Map to expected keys (handle variations)
                $qualificationMap = [
                    'phd' => 'phd',
                    'ph.d' => 'phd',
                    'doctorofphilosophy' => 'phd',
                    'msc' => 'msc',
                    'm.sc' => 'msc',
                    'masters' => 'msc',
                    'masterofscience' => 'msc',
                    'pgd' => 'pgd',
                    'pg.d' => 'pgd',
                    'postgraduatediploma' => 'pgd',
                ];
                
                $qualificationKey = $qualificationMap[$qualificationNormalized] ?? $qualificationNormalized;
                
                // Try qualification-specific key (e.g., 'postgraduate-phd', 'postgraduate-msc', 'postgraduate-pgd')
                $postgradQualKey = 'postgraduate-' . $qualificationKey;
                if (isset($feeTypeServiceCodes[$postgradQualKey])) {
                    $serviceCode = $feeTypeServiceCodes[$postgradQualKey];
                }
            }
            
            // If not resolved yet, try category-specific service code for non-postgraduate categories
            if (empty($serviceCode) && $categorySlug && isset($feeTypeServiceCodes[$categorySlug])) {
                $serviceCode = $feeTypeServiceCodes[$categorySlug];
            }

            // EOI and other flat fee types use a single default service code
            if (empty($serviceCode) && isset($feeTypeServiceCodes['default'])) {
                $serviceCode = $feeTypeServiceCodes['default'];
            }
        } else {
            $serviceCode = null;
        }
        
        if (empty($serviceCode)) {
            $expectedKey = null;
            if ($categorySlug === 'postgraduate' && $qualificationType) {
                $qualificationNormalized = strtolower(str_replace(['.', ' ', '_'], '', trim($qualificationType)));
                $qualificationMap = [
                    'phd' => 'phd', 'ph.d' => 'phd', 'doctorofphilosophy' => 'phd',
                    'msc' => 'msc', 'm.sc' => 'msc', 'masters' => 'msc', 'masterofscience' => 'msc',
                    'pgd' => 'pgd', 'pg.d' => 'pgd', 'postgraduatediploma' => 'pgd',
                ];
                $qualificationKey = $qualificationMap[$qualificationNormalized] ?? $qualificationNormalized;
                $expectedKey = 'postgraduate-' . $qualificationKey;
            } else {
                $expectedKey = $categorySlug;
            }
            
            Log::error('No service code configured for fee type and category', [
                'fee_type_code' => $feeTypeCode,
                'category_slug' => $categorySlug,
                'qualification_type' => $qualificationType,
                'expected_key' => $expectedKey,
                'transaction_id' => $transaction->id,
                'available_codes' => is_array($feeTypeServiceCodes) ? array_keys($feeTypeServiceCodes) : null
            ]);
            throw new \Exception("No service code configured for this payment type and category combination. Each category must have its own service code. Please contact the administrator.");
        }

        // Log which service code is being used
        Log::info('Credo Central service code resolved', [
            'transaction_id' => $transaction->id,
            'fee_type_code' => $feeTypeCode,
            'category_slug' => $categorySlug,
            'qualification_type' => $qualificationType,
            'service_code' => $serviceCode,
            'amount' => $transaction->amount
        ]);

        $requestData = [
            'amount' => $transaction->amount * 100,
            'email' => $transaction->alumni->user->email,
            'bearer' => 0,
            'callbackUrl' => route('alumni.payments.redirect'),
            'channels' => ['card', 'bank'],
            'currency' => 'NGN',
            'customerFirstName' => explode(' ', $transaction->alumni->user->name)[0] ?? '',
            'customerLastName' => explode(' ', $transaction->alumni->user->name)[1] ?? '',
            'customerPhoneNumber' => $transaction->alumni->phone_number,
            'reference' => $transaction->payment_reference,
            'serviceCode' => $serviceCode,
            'metadata' => [
                'customFields' => [
                    [
                        'variable_name' => 'fee_type',
                        'value' => $transaction->feeTemplate->feeType->code,
                        'display_name' => 'Fee Type'
                    ],
                    [
                        'variable_name' => 'category',
                        'value' => $categorySlug,
                        'display_name' => 'Category'
                    ],
                    [
                        'variable_name' => 'alumni_id',
                        'value' => $transaction->alumni_id,
                        'display_name' => 'Alumni ID'
                    ],
                    [
                        'variable_name' => 'transaction_id',
                        'value' => $transaction->id,
                        'display_name' => 'Transaction ID'
                    ]
                ]
            ]
        ];

        try {
            // Optional health check — skip 404 logging
            try {
                $healthCheck = $this->getHttpClient()->get($this->baseUrl . '/health');
                if ($healthCheck->successful()) {
                    Log::info('Credo Central API health check', [
                        'status' => $healthCheck->status(),
                        'body' => $healthCheck->body()
                    ]);
                }
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), '404')) {
                    Log::warning('Credo Central API health check failed', [
                        'error' => $e->getMessage(),
                        'url' => $this->baseUrl . '/health'
                    ]);
                }
            }

            Log::info('Credo Central API Request', [
                'url' => $fullUrl,
                'request_data' => $requestData,
                'transaction_id' => $transaction->id,
            ]);

            $response = $this->getHttpClient()->post($fullUrl, $requestData);
            $responseData = $response->json();

            Log::info('Credo Central API Response', [
                'transaction_id' => $transaction->id,
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->failed() || empty($responseData)) {
                throw new \Exception('Payment provider returned a failed response or empty payload.');
            }

            // Correct key: check `authorizationUrl` not `authorization_url`
            $authUrl = $responseData['data']['authorizationUrl'] ?? null;

            if (!$authUrl) {
                throw new \Exception('Payment authorization URL not found in response');
            }

            $credoReference = $responseData['data']['credoReference']
                ?? $responseData['data']['transRef']
                ?? $responseData['data']['reference']
                ?? null;

            $transaction->update([
                'payment_link' => $authUrl,
                'payment_provider' => 'credocentral',
                'payment_provider_reference' => $credoReference,
            ]);

            Log::info('Credo Central payment initialized successfully', [
                'transaction_id' => $transaction->id,
                'payment_link' => $authUrl
            ]);

            return $authUrl;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error with Credo Central API', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'url' => $fullUrl,
                'request_data' => $requestData
            ]);
            throw new \Exception('Unable to connect to payment provider. Please try again later.');
        } catch (\Throwable $e) {
            Log::error('Credo Central payment initialization error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $requestData
            ]);
            throw $e;
        }
    }

    /**
     * Verify a payment transaction with Credo's /transaction/{transRef}/verify endpoint.
     */
    public function verifyPayment(
        Transaction $transaction,
        ?string $credoReference = null,
        bool $adminReconciliation = false
    ) {
        try {
            $transRef = $credoReference ?: $transaction->payment_provider_reference;

            if (!$transRef) {
                throw new \Exception('No Credo transaction reference available for verification.');
            }

            Log::info('Starting payment verification', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference,
                'provider_reference' => $transRef,
                'current_status' => $transaction->status,
                'admin_reconciliation' => $adminReconciliation,
            ]);

            $verifyUrl = $this->baseUrl . '/transaction/' . rawurlencode($transRef) . '/verify';
            $response = $this->getSecretHttpClient()->get($verifyUrl);

            if ($response->successful()) {
                $data = $response->json();
                $payload = $data['data'] ?? [];

                Log::info('Credo Central payment verification response', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->payment_reference,
                    'raw_response' => $data,
                    'status_from_provider' => $payload['status'] ?? 'unknown',
                ]);

                $rawStatus = $payload['status']
                    ?? $payload['transactionStatus']
                    ?? $payload['paymentStatus']
                    ?? '';
                $status = strtolower((string) $rawStatus);
                $isSuccessStatus = $this->isSuccessfulStatus($rawStatus);
                $isFailedStatus = $this->isFailedStatus($rawStatus);
                $amountMatches = $this->amountMatches($transaction, $payload);
                $referenceMatches = $this->referenceMatches($transaction, $payload, $transRef);

                $transAmountKobo = $this->resolveTransAmountKobo($payload);
                $returnedNaira = $transAmountKobo !== null ? $transAmountKobo / 100 : null;

                $paid = $isSuccessStatus && $amountMatches && $referenceMatches;
                if ($adminReconciliation && $isSuccessStatus && $amountMatches) {
                    $paid = true;
                }

                Log::info('Payment status verification result', [
                    'transaction_id' => $transaction->id,
                    'original_status' => $status,
                    'is_success_status' => $isSuccessStatus,
                    'is_failed_status' => $isFailedStatus,
                    'amount_matches' => $amountMatches,
                    'reference_matches' => $referenceMatches,
                    'paid' => $paid,
                    'returned_amount_kobo' => $transAmountKobo,
                    'expected_amount_kobo' => (int) round(((float) $transaction->amount) * 100),
                    'provider_reference' => $transRef,
                    'payment_reference' => $transaction->payment_reference,
                    'business_ref' => $payload['businessRef'] ?? $payload['reference'] ?? null,
                ]);

                return [
                    'status' => $status,
                    'paid' => $paid,
                    'is_success_status' => $isSuccessStatus,
                    'is_failed_status' => $isFailedStatus,
                    'amount_matches' => $amountMatches,
                    'reference_matches' => $referenceMatches,
                    'expected_amount' => (float) $transaction->amount,
                    'returned_amount' => $returnedNaira,
                    'business_ref' => $payload['businessRef'] ?? $payload['reference'] ?? null,
                    'expected_reference' => $transaction->payment_reference,
                    'verified_with_reference' => $transRef,
                    'amount' => $returnedNaira ?? 0,
                    'paid_at' => $payload['transactionDate'] ?? $payload['paid_at'] ?? null,
                    'raw_data' => $payload,
                ];
            }

            Log::error('Credo Central payment verification failed', [
                'transaction_id' => $transaction->id,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to verify payment: ' . ($response->json()['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Credo Central payment verification error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Try multiple Credo references for admin reconciliation.
     */
    public function verifyPaymentForAdminReconciliation(Transaction $transaction, ?string $primaryReference = null): array
    {
        $references = array_values(array_unique(array_filter([
            $primaryReference,
            $transaction->payment_provider_reference,
            $transaction->payment_reference,
        ])));

        if ($references === []) {
            throw new \Exception('No Credo transaction reference available for verification.');
        }

        $lastVerification = null;
        $lastException = null;

        foreach ($references as $reference) {
            try {
                $verification = $this->verifyPayment($transaction, $reference, true);
                $lastVerification = $verification;

                if (!empty($verification['paid'])) {
                    return $verification;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning('Admin reconciliation verify attempt failed', [
                    'transaction_id' => $transaction->id,
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($lastVerification) {
            return $lastVerification;
        }

        throw $lastException ?? new \Exception('Unable to verify payment with Credo Central.');
    }

    protected function referenceMatches(Transaction $transaction, array $payload, string $transRef): bool
    {
        $businessRef = $payload['businessRef'] ?? $payload['reference'] ?? null;
        $payloadTransRef = $payload['transRef'] ?? $payload['credoReference'] ?? null;

        if ($businessRef && strcasecmp(trim((string) $businessRef), trim($transaction->payment_reference)) === 0) {
            return true;
        }

        if ($payloadTransRef && strcasecmp(trim((string) $payloadTransRef), trim($transRef)) === 0) {
            return true;
        }

        return $businessRef === null && $payloadTransRef === null;
    }

    protected function isSuccessfulStatus(mixed $rawStatus): bool
    {
        if (is_numeric($rawStatus)) {
            return (int) $rawStatus === 0;
        }

        return in_array(strtolower((string) $rawStatus), [
            'success',
            'successful',
            'paid',
            'completed',
            '0',
        ], true);
    }

    protected function isFailedStatus(mixed $rawStatus): bool
    {
        if (is_numeric($rawStatus)) {
            return in_array((int) $rawStatus, [3, 7, 9, 10], true);
        }

        return in_array(strtolower((string) $rawStatus), [
            'failed',
            'declined',
            'cancelled',
            'canceled',
        ], true);
    }

    protected function amountMatches(Transaction $transaction, array $payload): bool
    {
        $expectedNaira = round((float) $transaction->amount, 2);
        $expectedKobo = (int) round($expectedNaira * 100);

        $candidates = array_filter([
            $payload['transAmount'] ?? null,
            $payload['settlementAmount'] ?? null,
            $payload['amount'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        if ($candidates === []) {
            return true;
        }

        foreach ($candidates as $raw) {
            $value = (float) $raw;

            if ((int) round($value) === $expectedKobo) {
                return true;
            }

            if (abs($value - $expectedNaira) < 0.01) {
                return true;
            }

            if (abs(($value / 100) - $expectedNaira) < 0.01) {
                return true;
            }
        }

        return false;
    }

    protected function resolveTransAmountKobo(array $payload): ?int
    {
        $expectedFields = ['transAmount', 'settlementAmount', 'amount'];

        foreach ($expectedFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                continue;
            }

            $value = (float) $payload[$field];

            if ($value >= 100000) {
                return (int) round($value);
            }

            if (fmod($value, 1.0) !== 0.0) {
                return (int) round($value * 100);
            }

            if ($value >= 1000) {
                return (int) round($value);
            }

            return (int) round($value * 100);
        }

        return null;
    }

    /**
     * Handle webhook notifications
     */
    public function handleWebhook(array $payload)
    {
        try {
            // Verify webhook signature
            $signature = $payload['signature'] ?? null;
            if (!$this->verifyWebhookSignature($signature, $payload)) {
                throw new \Exception('Invalid webhook signature');
            }

            $event = $payload['event'] ?? null;
            $data = $payload['data'] ?? null;

            if (!$event || !$data) {
                throw new \Exception('Invalid webhook payload');
            }

            Log::info('Credo Central webhook received', [
                'event' => $event,
                'data' => $data
            ]);

            $transaction = $this->findTransactionFromProviderPayload($data);
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            switch ($event) {
                case 'transaction.successful':
                case 'charge.success':
                    $this->handleSuccessfulPayment($transaction, $data);
                    break;
                case 'transaction.failed':
                case 'charge.failed':
                    $this->handleFailedPayment($transaction, $data);
                    break;
                default:
                    Log::info('Unhandled webhook event', ['event' => $event]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Credo Central webhook handling error', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw $e;
        }
    }

    protected function findTransactionFromProviderPayload(array $data): ?Transaction
    {
        $providerRef = $data['transRef']
            ?? $data['credoReference']
            ?? $data['reference']
            ?? null;
        $businessRef = $data['businessRef'] ?? null;

        if ($providerRef) {
            $transaction = Transaction::where('payment_provider_reference', $providerRef)->first();
            if ($transaction) {
                return $transaction;
            }
        }

        if ($businessRef) {
            return Transaction::where('payment_reference', $businessRef)->first();
        }

        return null;
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature($signature, $payload)
    {
        if (!$signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', json_encode($payload), $this->secretKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(Transaction $transaction, array $data)
    {
        $this->paymentCompletion->complete(
            $transaction,
            $data['paid_at'] ?? null,
            [
                'webhook_received_at' => now()->toIso8601String(),
                'webhook_data' => $data,
            ]
        );
    }

    /**
     * Handle failed payment
     */
    protected function handleFailedPayment(Transaction $transaction, array $data)
    {
        try {
            $transaction->update([
                'status' => 'failed',
                'payment_details' => json_encode($data)
            ]);

            Log::info('Payment failed', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference,
                'reason' => $data['reason'] ?? 'Unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle failed payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 