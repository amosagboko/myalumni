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
        $this->baseUrl = config('services.credocentral.base_url', 'https://api.credocentral.com');
        $this->publicKey = config('services.credocentral.public_key');
        $this->secretKey = config('services.credocentral.secret_key');
    }

    /**
     * Get HTTP client with proper configuration
     */
    protected function getHttpClient()
    {
        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json'
        ])
        ->timeout(30); // Set a 30-second timeout

        // Add retry logic with proper error handling
        $client->retry(3, 100, function ($exception) {
            Log::warning('Retrying Credo Central API request', [
                'error' => $exception->getMessage(),
                'url' => $this->baseUrl
            ]);
            return $exception instanceof \Illuminate\Http\Client\ConnectionException;
        });

        // Always verify SSL certificates for security
        return $client;
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(Transaction $transaction)
    {
        try {
            $endpoint = '/v1/transaction/initialize';
            $fullUrl = $this->baseUrl . $endpoint;
            
            // Log the exact URL and request details
            Log::info('Credo Central API Request Details', [
                'base_url' => $this->baseUrl,
                'endpoint' => $endpoint,
                'full_url' => $fullUrl,
                'method' => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($this->secretKey, 0, 10) . '...',
                    'Content-Type' => 'application/json'
                ],
                'has_public_key' => !empty($this->publicKey),
                'has_secret_key' => !empty($this->secretKey),
                'service_code' => config('services.credocentral.service_code'),
                'environment' => app()->environment()
            ]);

            $requestData = [
                'amount' => $transaction->amount * 100, // Convert to kobo
                'currency' => 'NGN',
                'reference' => $transaction->payment_reference,
                'callback_url' => route('alumni.payments.webhook'),
                'service_code' => config('services.credocentral.service_code', 'ALUMNI_PAYMENT'),
                'customer' => [
                    'name' => $transaction->alumni->user->name,
                    'email' => $transaction->alumni->user->email,
                    'phone' => $transaction->alumni->phone_number
                ],
                'metadata' => [
                    'transaction_id' => $transaction->id,
                    'fee_type' => $transaction->feeTemplate->feeType->code,
                    'alumni_id' => $transaction->alumni_id,
                    'service_code' => config('services.credocentral.service_code', 'ALUMNI_PAYMENT')
                ]
            ];

            // Log the complete request data
            Log::info('Credo Central API Request Payload', [
                'url' => $fullUrl,
                'request_data' => $requestData,
                'transaction_id' => $transaction->id,
                'payment_reference' => $transaction->payment_reference,
                'amount' => $transaction->amount,
                'fee_type' => $transaction->feeTemplate->feeType->code
            ]);

            try {
                $response = $this->getHttpClient()->post($fullUrl, $requestData);
                
                // Log the complete response details
                Log::info('Credo Central API Response Details', [
                    'transaction_id' => $transaction->id,
                    'status_code' => $response->status(),
                    'raw_body' => $response->body(),
                    'headers' => $response->headers(),
                    'request_url' => $fullUrl,
                    'request_data' => $requestData,
                    'response_time' => $response->handlerStats()['total_time'] ?? null,
                    'has_public_key' => !empty($this->publicKey),
                    'has_secret_key' => !empty($this->secretKey),
                    'service_code' => config('services.credocentral.service_code'),
                    'environment' => app()->environment()
                ]);

                // Try to parse JSON response
                $responseData = null;
                try {
                    $responseData = $response->json();
                    Log::info('Credo Central API Response JSON', [
                        'transaction_id' => $transaction->id,
                        'parsed_response' => $responseData
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to parse JSON response', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                        'raw_body' => $response->body()
                    ]);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Failed to connect to Credo Central API', [
                    'error' => $e->getMessage(),
                    'url' => $this->baseUrl . $endpoint,
                    'transaction_id' => $transaction->id,
                    'request_data' => $requestData
                ]);
                throw new \Exception('Unable to connect to payment provider. Please try again later.');
            }

            if ($response->successful()) {
                if (!$responseData) {
                    throw new \Exception('Invalid response format from payment provider');
                }

                Log::info('Credo Central payment initialized', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->payment_reference,
                    'service_code' => config('services.credocentral.service_code', 'ALUMNI_PAYMENT'),
                    'response' => $responseData
                ]);

                // Update transaction with payment link
                if (!isset($responseData['data']['authorization_url'])) {
                    throw new \Exception('Payment authorization URL not found in response');
                }

                $transaction->update([
                    'payment_link' => $responseData['data']['authorization_url'],
                    'payment_provider' => 'credocentral',
                    'payment_provider_reference' => $responseData['data']['reference'] ?? null
                ]);

                return $responseData['data']['authorization_url'];
            }

            // Handle error response
            $errorMessage = 'Unknown error';
            if ($responseData) {
                if (isset($responseData['message'])) {
                    $errorMessage = $responseData['message'];
                } elseif (isset($responseData['error'])) {
                    $errorMessage = $responseData['error'];
                } elseif (isset($responseData['errors'])) {
                    $errorMessage = is_array($responseData['errors']) ? implode(', ', $responseData['errors']) : $responseData['errors'];
                }
            } else {
                // If we couldn't parse JSON, use the raw response
                $errorMessage = $response->body() ?: 'Empty response from payment provider';
            }

            Log::error('Credo Central payment initialization failed', [
                'transaction_id' => $transaction->id,
                'status_code' => $response->status(),
                'error_message' => $errorMessage,
                'raw_response' => $response->body(),
                'request_data' => $requestData,
                'request_url' => $this->baseUrl . $endpoint,
                'headers' => $response->headers()
            ]);

            throw new \Exception('Failed to initialize payment: ' . $errorMessage);

        } catch (\Exception $e) {
            Log::error('Credo Central payment initialization error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $requestData ?? null
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
            $response = $this->getHttpClient()->get($this->baseUrl . '/v1/transactions/' . $transaction->payment_provider_reference);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Credo Central payment verification', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->payment_reference,
                    'response' => $data
                ]);

                return [
                    'status' => $data['data']['status'],
                    'paid' => $data['data']['status'] === 'success',
                    'amount' => $data['data']['amount'] / 100, // Convert from kobo to naira
                    'paid_at' => $data['data']['paid_at'] ?? null
                ];
            }

            Log::error('Credo Central payment verification failed', [
                'transaction_id' => $transaction->id,
                'response' => $response->json()
            ]);

            throw new \Exception('Failed to verify payment: ' . ($response->json()['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            Log::error('Credo Central payment verification error', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
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
                'status' => 'completed',
                'paid_at' => now(),
                'payment_details' => json_encode($data)
            ]);

            // Handle any post-payment actions (e.g., update candidate status for screening fees)
            if ($transaction->feeTemplate->feeType->code === 'screening_fee') {
                $candidate = \App\Models\Candidate::where('alumni_id', $transaction->alumni_id)
                    ->where('has_paid_screening_fee', false)
                    ->latest()
                    ->first();

                if ($candidate) {
                    $candidate->update(['has_paid_screening_fee' => true]);
                }
            }

            Log::info('Payment completed successfully', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference
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