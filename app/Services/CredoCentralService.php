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

    public function __construct()
    {
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
     * Initialize a payment transaction
     */
    public function initializePayment(Transaction $transaction)
    {
        $endpoint = '/transaction/initialize';
        $fullUrl = $this->baseUrl . $endpoint;

        // Determine the service code based on the fee type code (no fallback)
        $feeTypeCode = $transaction->feeTemplate->feeType->code;
        $serviceCode = config('services.credocentral.service_codes.' . $feeTypeCode);
        if (empty($serviceCode)) {
            Log::error('No service code configured for fee type', [
                'fee_type_code' => $feeTypeCode,
                'transaction_id' => $transaction->id
            ]);
            throw new \Exception("No service code configured for this payment type. Please contact the administrator.");
        }

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

            $transaction->update([
                'payment_link' => $authUrl,
                'payment_provider' => 'credocentral',
                'payment_provider_reference' => $responseData['data']['reference'] ?? null
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
     * Verify a payment transaction
     */
    public function verifyPayment(Transaction $transaction)
    {
        try {
            Log::info('Starting payment verification', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference,
                'provider_reference' => $transaction->payment_provider_reference,
                'current_status' => $transaction->status
            ]);
    
            $response = $this->getHttpClient()->get($this->baseUrl . '/transaction/' . $transaction->payment_provider_reference);
    
            if ($response->successful()) {
                $data = $response->json();
    
                Log::info('Credo Central payment verification response', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->payment_reference,
                    'raw_response' => $data,
                    'status_from_provider' => $data['data']['status'] ?? 'unknown'
                ]);
    
                // Normalize status
                $rawStatus = $data['data']['status'] ?? '';
                $status = strtolower((string) $rawStatus);
    
                // Debug status type
                Log::debug('Payment status normalization', [
                    'transaction_id' => $transaction->id,
                    'raw_status' => $rawStatus,
                    'normalized_status' => $status,
                    'status_type' => gettype($rawStatus)
                ]);
    
                // Define valid success values
                $successStatuses = ['success', 'paid', 'completed', '0'];
    
                // Use strict comparison to avoid false positives
                $isPaid = in_array($status, $successStatuses, true);
    
                // Optional: Confirm amount matches expected
                $returnedAmount = ($data['data']['amount'] ?? 0) / 100;
                $amountMatches = $returnedAmount == $transaction->amount;
    
                Log::info('Payment status verification result', [
                    'transaction_id' => $transaction->id,
                    'original_status' => $status,
                    'is_paid' => $isPaid,
                    'amount_matches' => $amountMatches,
                    'returned_amount' => $returnedAmount,
                    'expected_amount' => $transaction->amount,
                    'provider_reference' => $transaction->payment_provider_reference,
                    'payment_reference' => $transaction->payment_reference
                ]);
    
                return [
                    'status' => $status,
                    'paid' => $isPaid && $amountMatches, // mark as paid only if status and amount match
                    'amount' => $returnedAmount,
                    'paid_at' => $data['data']['paid_at'] ?? $data['data']['created_at'] ?? null,
                    'raw_data' => $data['data']
                ];
            }
    
            Log::error('Credo Central payment verification failed', [
                'transaction_id' => $transaction->id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
    
            throw new \Exception('Failed to verify payment: ' . ($response->json()['message'] ?? 'Unknown error'));
    
        } catch (\Exception $e) {
            Log::error('Credo Central payment verification error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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

            // Find the transaction
            $transaction = Transaction::where('payment_provider_reference', $data['reference'])->first();
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            // Handle different event types
            switch ($event) {
                case 'charge.success':
                    $this->handleSuccessfulPayment($transaction, $data);
                    break;
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
        try {
            $transaction->update([
                'status' => 'paid',
                'paid_at' => $data['paid_at'] ?? now(),
                'payment_details' => array_merge(
                    $transaction->payment_details ?? [],
                    [
                        'webhook_received_at' => now(),
                        'webhook_data' => $data
                    ]
                )
            ]);

            // Handle any post-payment actions (e.g., update candidate status for screening fees)
            if ($transaction->feeTemplate->feeType->code === 'screening_fee') {
                $meta = $transaction->metadata ? (is_array($transaction->metadata) ? $transaction->metadata : json_decode($transaction->metadata, true)) : [];
                $candidateId = $meta['candidate_id'] ?? null;
                $electionId = $meta['election_id'] ?? null;
                $officeId = $meta['office_id'] ?? null;
                $manifesto = $meta['manifesto'] ?? null;
                $passport = $meta['passport'] ?? null;
                $documents = $meta['documents'] ?? [];
                $candidate = null;
                if ($candidateId) {
                    $candidate = \App\Models\Candidate::find($candidateId);
                }
                if (!$candidate && $electionId && $officeId) {
                    // fallback for legacy/edge cases
                    $candidate = \App\Models\Candidate::where('alumni_id', $transaction->alumni_id)
                        ->where('election_id', $electionId)
                        ->where('election_office_id', $officeId)
                        ->first();
                }
                if ($candidate) {
                    $candidate->update([
                        'has_paid_screening_fee' => true,
                        'status' => 'paid',
                    ]);
                } else if ($electionId && $officeId) {
                    // fallback: create if not found (should not happen)
                    \App\Models\Candidate::create([
                        'election_id' => $electionId,
                        'election_office_id' => $officeId,
                        'alumni_id' => $transaction->alumni_id,
                        'has_paid_screening_fee' => true,
                        'manifesto' => $manifesto,
                        'passport' => $passport,
                        'documents' => $documents,
                        'status' => 'paid',
                    ]);
                }
            }

            Log::info('Payment completed successfully', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference,
                'paid_at' => $transaction->paid_at
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle successful payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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