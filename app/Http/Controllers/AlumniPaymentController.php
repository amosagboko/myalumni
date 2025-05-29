<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Transaction;
use App\Models\FeeTemplate;
use App\Services\CredoCentralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AlumniPaymentController extends Controller
{
    protected $credocentral;

    public function __construct(CredoCentralService $credocentral)
    {
        $this->credocentral = $credocentral;
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $alumni = $user->alumni;
        $fees = $alumni->getActiveFees();
        
        return view('alumni.payments.index', compact('fees'));
    }

    /**
     * Initiate a payment transaction
     */
    public function initiatePayment(Request $request)
    {
        try {
            Log::info('Starting payment initiation', [
                'request' => $request->all(),
                'user_id' => Auth::id(),
                'alumni_id' => Auth::user()->alumni->id ?? null,
                'fee_id' => $request->fee_id,
                'service_code' => config('services.credocentral.service_code')
            ]);

            $request->validate([
                'fee_id' => 'required|exists:fee_templates,id'
            ]);

            /** @var User $user */
            $user = Auth::user();
            $alumni = $user->alumni;

            Log::info('Checking alumni information', [
                'alumni_id' => $alumni->id,
                'has_phone' => !empty($alumni->phone_number),
                'has_email' => !empty($user->email),
                'phone_number' => $alumni->phone_number,
                'email' => $user->email,
                'name' => $user->name,
                'graduation_year' => $alumni->year_of_graduation,
                'fee_id' => $request->fee_id
            ]);

            // Validate required alumni information
            if (!$alumni->phone_number) {
                Log::warning('Missing alumni phone number', [
                    'alumni_id' => $alumni->id,
                    'user_id' => $user->id,
                    'fee_id' => $request->fee_id
                ]);
                return redirect()->back()->with('error', 'Please update your phone number in your profile before making a payment.');
            }

            if (!$user->email) {
                Log::warning('Missing user email', [
                    'alumni_id' => $alumni->id,
                    'user_id' => $user->id,
                    'fee_id' => $request->fee_id
                ]);
                return redirect()->back()->with('error', 'Please update your email address in your profile before making a payment.');
            }

            $fee = FeeTemplate::with('feeType')->findOrFail($request->fee_id);

            Log::info('Found fee details', [
                'fee_id' => $fee->id,
                'fee_type' => $fee->feeType->code,
                'fee_amount' => $fee->amount,
                'is_active' => $fee->is_active,
                'alumni_year' => $fee->graduation_year,
                'alumni_graduation_year' => $alumni->year_of_graduation,
                'alumni_phone' => $alumni->phone_number,
                'alumni_email' => $user->email,
                'service_code' => config('services.credocentral.service_code')
            ]);

            // Check if fee is active
            if (!$fee->is_active) {
                Log::warning('Attempted to pay inactive fee', [
                    'fee_id' => $fee->id,
                    'fee_type' => $fee->feeType->code,
                    'alumni_id' => $alumni->id
                ]);
                return redirect()->back()->with('error', 'This fee is currently inactive.');
            }

            // For subscription fees, only check if alumni graduated in 2023 or earlier
            if ($fee->feeType->code === 'subscription') {
                if ($alumni->year_of_graduation > 2023) {
                    Log::warning('Subscription fee not applicable to recent graduates', [
                        'fee_id' => $fee->id,
                        'fee_type' => $fee->feeType->code,
                        'alumni_year' => $alumni->year_of_graduation
                    ]);
                    return redirect()->back()->with('error', 'Subscription fees are only applicable to alumni who graduated in 2023 or earlier.');
                }
            } else {
                // For non-subscription fees, check if fee is applicable to alumni's graduation year
                if ($fee->graduation_year !== $alumni->year_of_graduation) {
                    Log::warning('Year mismatch for fee payment', [
                        'fee_id' => $fee->id,
                        'fee_type' => $fee->feeType->code,
                        'fee_year' => $fee->graduation_year,
                        'alumni_year' => $alumni->year_of_graduation
                    ]);
                    return redirect()->back()->with('error', 'This fee is not applicable to your graduation year.');
                }
            }

            // Check for existing pending transaction
            $existingTransaction = Transaction::where('alumni_id', $alumni->id)
                ->where('fee_template_id', $fee->id)
                ->where('status', 'pending')
                ->first();

            Log::info('Checking for existing transaction', [
                'alumni_id' => $alumni->id,
                'fee_id' => $fee->id,
                'found_existing' => !is_null($existingTransaction),
                'existing_transaction_id' => $existingTransaction?->id,
                'existing_status' => $existingTransaction?->status,
                'existing_payment_link' => $existingTransaction?->payment_link
            ]);

            if ($existingTransaction) {
                Log::info('Found existing pending transaction, attempting to reinitialize payment', [
                    'transaction_id' => $existingTransaction->id,
                    'payment_reference' => $existingTransaction->payment_reference
                ]);
                // If there's an existing payment link, redirect to it
                if ($existingTransaction->payment_link) {
                    Log::info('Redirecting to existing payment link', [
                        'transaction_id' => $existingTransaction->id,
                        'payment_link' => $existingTransaction->payment_link
                    ]);
                    return redirect($existingTransaction->payment_link);
                }
                // Otherwise, initialize a new payment for the existing transaction
                try {
                    Log::info('Initializing payment for existing transaction', [
                        'transaction_id' => $existingTransaction->id,
                        'amount' => $existingTransaction->amount,
                        'reference' => $existingTransaction->payment_reference
                    ]);
                    $paymentLink = $this->credocentral->initializePayment($existingTransaction);
                    Log::info('Successfully initialized payment for existing transaction', [
                        'transaction_id' => $existingTransaction->id,
                        'payment_link' => $paymentLink
                    ]);
                    return redirect($paymentLink);
                } catch (\Exception $e) {
                    Log::error('Failed to initialize payment for existing transaction', [
                        'transaction_id' => $existingTransaction->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Create new transaction
            DB::beginTransaction();
            try {
                Log::info('Starting transaction creation', [
                    'alumni_id' => $alumni->id,
                    'fee_id' => $fee->id,
                    'amount' => $fee->amount,
                    'payment_reference' => 'ALUMNI-' . strtoupper(Str::random(10)),
                    'alumni_name' => $alumni->user->name,
                    'alumni_email' => $alumni->user->email,
                    'alumni_phone' => $alumni->phone_number,
                    'fee_type' => $fee->feeType->code,
                    'fee_description' => $fee->description,
                    'graduation_year' => $fee->graduation_year
                ]);

                $transaction = Transaction::create([
                    'alumni_id' => $alumni->id,
                    'fee_template_id' => $fee->id,
                    'amount' => $fee->amount,
                    'payment_reference' => 'ALUMNI-' . strtoupper(Str::random(10)),
                    'status' => 'pending',
                    'payment_provider' => 'credocentral',
                    'payment_details' => [
                        'fee_type' => $fee->feeType->code,
                        'fee_description' => $fee->description,
                        'graduation_year' => $fee->graduation_year,
                        'alumni_name' => $alumni->user->name,
                        'alumni_email' => $alumni->user->email,
                        'alumni_phone' => $alumni->phone_number
                    ]
                ]);

                Log::info('Transaction created successfully', [
                    'transaction_id' => $transaction->id,
                    'payment_reference' => $transaction->payment_reference,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at,
                    'payment_details' => $transaction->payment_details,
                    'service_code' => config('services.credocentral.service_code'),
                    'alumni_id' => $alumni->id,
                    'fee_id' => $fee->id
                ]);

                // Initialize payment with Credo Central
                Log::info('Starting payment initialization with Credo Central', [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'reference' => $transaction->payment_reference,
                    'service_code' => config('services.credocentral.service_code'),
                    'alumni_id' => $alumni->id,
                    'fee_id' => $fee->id,
                    'customer_name' => $transaction->alumni->user->name,
                    'customer_email' => $transaction->alumni->user->email,
                    'customer_phone' => $transaction->alumni->phone_number,
                    'base_url' => config('services.credocentral.base_url'),
                    'has_public_key' => !empty(config('services.credocentral.public_key')),
                    'has_secret_key' => !empty(config('services.credocentral.secret_key')),
                    'environment' => app()->environment()
                ]);
                
                try {
                    $paymentLink = $this->credocentral->initializePayment($transaction);
                    
                    Log::info('Payment initialized successfully', [
                        'transaction_id' => $transaction->id,
                        'payment_link' => $paymentLink,
                        'alumni_id' => $alumni->id,
                        'fee_id' => $fee->id,
                        'payment_reference' => $transaction->payment_reference
                    ]);

                    DB::commit();
                    return redirect($paymentLink);
                } catch (\Exception $e) {
                    Log::error('Failed to initialize payment with Credo Central', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'payment_reference' => $transaction->payment_reference,
                        'amount' => $transaction->amount,
                        'service_code' => config('services.credocentral.service_code'),
                        'base_url' => config('services.credocentral.base_url'),
                        'has_public_key' => !empty(config('services.credocentral.public_key')),
                        'has_secret_key' => !empty(config('services.credocentral.secret_key')),
                        'environment' => app()->environment()
                    ]);
                    throw $e;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create or initialize payment', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'fee_id' => $fee->id,
                    'alumni_id' => $alumni->id,
                    'service_code' => config('services.credocentral.service_code'),
                    'alumni_phone' => $alumni->phone_number,
                    'alumni_email' => $user->email,
                    'alumni_name' => $user->name,
                    'base_url' => config('services.credocentral.base_url'),
                    'has_public_key' => !empty(config('services.credocentral.public_key')),
                    'has_secret_key' => !empty(config('services.credocentral.secret_key')),
                    'environment' => app()->environment()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'alumni_id' => Auth::user()->alumni->id ?? null,
                'fee_id' => $request->fee_id ?? null,
                'service_code' => config('services.credocentral.service_code')
            ]);

            return redirect()->back()->with('error', 'Failed to initiate payment. Please try again.');
        }
    }

    /**
     * Handle payment webhook
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Payment webhook received', ['payload' => $request->all()]);

            $this->credocentral->handleWebhook($request->all());

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request, Transaction $transaction)
    {
        try {
            // Verify payment with Credo Central
            $result = $this->credocentral->verifyPayment($transaction);

            if ($result['paid']) {
                return redirect()->route('alumni.payments.success', $transaction)
                    ->with('success', 'Payment verified successfully.');
            }

            return redirect()->route('alumni.payments.pending', $transaction)
                ->with('info', 'Payment is still pending. Please complete the payment process.');

        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('alumni.payments.failed', $transaction)
                ->with('error', 'Failed to verify payment. Please contact support.');
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return redirect()->route('alumni.payments.pending', $transaction);
        }

        return view('payments.success', compact('transaction'));
    }

    /**
     * Show payment pending page
     */
    public function paymentPending(Transaction $transaction)
    {
        if ($transaction->status === 'completed') {
            return redirect()->route('alumni.payments.success', $transaction);
        }

        if ($transaction->status === 'failed') {
            return redirect()->route('alumni.payments.failed', $transaction);
        }

        return view('payments.pending', compact('transaction'));
    }

    /**
     * Show payment failed page
     */
    public function paymentFailed(Transaction $transaction)
    {
        if ($transaction->status === 'completed') {
            return redirect()->route('alumni.payments.success', $transaction);
        }

        return view('payments.failed', compact('transaction'));
    }

    public function show(Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated alumni
        if ($transaction->alumni_id !== Auth::user()->alumni->id) {
            Log::warning('Unauthorized access attempt to transaction', [
                'transaction_id' => $transaction->id,
                'alumni_id' => $transaction->alumni_id,
                'user_id' => Auth::id()
            ]);
            abort(403, 'You are not authorized to view this transaction.');
        }

        return view('alumni.payments.show', compact('transaction'));
    }

    /**
     * Handle demo payment processing consistently across all payment methods
     */
    private function handleDemoPayment(Transaction $transaction, $redirectRoute = 'payments.index')
    {
        try {
            DB::beginTransaction();

            // Log the current state
            Log::info('Starting demo payment processing', [
                'transaction_id' => $transaction->id,
                'current_status' => $transaction->status,
                'is_test_mode' => $transaction->is_test_mode,
                'fee_type' => $transaction->feeTemplate->feeType->code ?? 'unknown'
            ]);

            // Check if transaction is already paid
            if ($transaction->status === 'paid') {
                Log::info('Transaction already paid', [
                    'transaction_id' => $transaction->id,
                    'paid_at' => $transaction->paid_at
                ]);
                return redirect()->route($redirectRoute)
                    ->with('info', 'This payment has already been completed.');
            }

            // Update transaction status
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            Log::info('Transaction status updated', [
                'transaction_id' => $transaction->id,
                'new_status' => 'paid',
                'paid_at' => now()
            ]);

            // If this is an EOI payment, update the candidate status
            if ($transaction->feeTemplate->feeType->code === 'screening_fee') {
                $candidate = \App\Models\Candidate::where('alumni_id', $transaction->alumni_id)
                    ->where('has_paid_screening_fee', false)
                    ->latest()
                    ->first();

                if ($candidate) {
                    $candidate->update([
                        'has_paid_screening_fee' => true
                    ]);
                    Log::info('Candidate payment status updated', [
                        'transaction_id' => $transaction->id,
                        'candidate_id' => $candidate->id,
                        'has_paid_screening_fee' => true
                    ]);
                } else {
                    Log::warning('No pending candidate found for EOI payment', [
                        'transaction_id' => $transaction->id,
                        'alumni_id' => $transaction->alumni_id
                    ]);
                }
            }

            DB::commit();

            // Set appropriate success message based on the fee type
            $successMessage = $transaction->feeTemplate->feeType->code === 'screening_fee'
                ? 'Payment completed successfully. Your expression of interest has been submitted.'
                : 'Demo payment completed successfully.';

            Log::info('Demo payment completed successfully', [
                'transaction_id' => $transaction->id,
                'redirect_route' => $redirectRoute,
                'success_message' => $successMessage
            ]);

            return redirect()->route($redirectRoute)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process demo payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'current_status' => $transaction->status,
                'is_test_mode' => $transaction->is_test_mode
            ]);
            return redirect()
                ->route('alumni.payments.show', $transaction)
                ->with('error', 'Failed to process payment. Please try again.');
        }
    }

    public function confirmPayment(Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated alumni
        if ($transaction->alumni_id !== Auth::user()->alumni->id) {
            Log::warning('Unauthorized payment confirmation attempt', [
                'transaction_id' => $transaction->id,
                'alumni_id' => $transaction->alumni_id,
                'user_id' => Auth::id()
            ]);
            abort(403);
        }

        // Log the transaction status for debugging
        Log::info('Confirming payment', [
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
            'fee_type' => $transaction->feeTemplate->feeType->code ?? 'unknown'
        ]);

        // Redirect to payment verification
        Log::info('Redirecting to payment verification', [
            'transaction_id' => $transaction->id,
            'fee_type' => $transaction->feeTemplate->feeType->code ?? 'unknown'
        ]);
        return redirect()->route('alumni.payments.show', ['transaction' => $transaction->id])
            ->with('info', 'Please verify your payment to complete the transaction.');
    }

    /**
     * Display payment history for the authenticated alumni.
     */
    public function history()
    {
        $alumni = Auth::user()->alumni;
        $transactions = Transaction::with(['feeTemplate.feeType', 'feeTemplate.alumniYear'])
            ->where('alumni_id', $alumni->id)
            ->latest()
            ->paginate(10);

        return view('alumni.payments.history', compact('transactions'));
    }

    public function processPayment(Transaction $transaction)
    {
        // Ensure the transaction belongs to the authenticated alumni
        if ($transaction->alumni_id !== Auth::user()->alumni->id) {
            Log::warning('Unauthorized access attempt to transaction', [
                'transaction_id' => $transaction->id,
                'alumni_id' => $transaction->alumni_id,
                'user_id' => Auth::id()
            ]);
            abort(403, 'You are not authorized to process this payment.');
        }

        // Show the payment confirmation page
        return view('alumni.payments.show', compact('transaction'));
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(Transaction $transaction, array $data)
    {
        try {
            DB::beginTransaction();

            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_details' => json_encode($data)
            ]);

            // If this is an EOI payment, update the candidate status
            if ($transaction->feeTemplate->feeType->code === 'screening_fee') {
                $candidate = \App\Models\Candidate::where('alumni_id', $transaction->alumni_id)
                    ->where('has_paid_screening_fee', false)
                    ->latest()
                    ->first();

                if ($candidate) {
                    $candidate->update([
                        'has_paid_screening_fee' => true
                    ]);
                    Log::info('Candidate payment status updated', [
                        'transaction_id' => $transaction->id,
                        'candidate_id' => $candidate->id,
                        'has_paid_screening_fee' => true
                    ]);
                }
            }

            DB::commit();

            Log::info('Payment completed successfully', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle successful payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment redirect after successful payment
     */
    public function handleRedirect(Request $request)
    {
        try {
            Log::info('Payment redirect received', [
                'reference' => $request->reference,
                'transRef' => $request->transRef,
                'status' => $request->status,
                'all_params' => $request->all()
            ]);

            // Find the transaction by payment reference
            $transaction = Transaction::where('payment_reference', $request->reference)
                ->orWhere('payment_provider_reference', $request->transRef)
                ->first();

            if (!$transaction) {
                Log::error('Transaction not found for redirect', [
                    'reference' => $request->reference,
                    'transRef' => $request->transRef
                ]);
                return redirect()->route('alumni.payments.index')
                    ->with('error', 'Transaction not found. Please contact support.');
            }

            // Update transaction with provider reference if not set
            if (!$transaction->payment_provider_reference && $request->transRef) {
                $transaction->update([
                    'payment_provider_reference' => $request->transRef
                ]);
            }

            // Verify payment status
            try {
                $result = $this->credocentral->verifyPayment($transaction);
                
                if ($result['paid']) {
                    return redirect()->route('alumni.payments.success', $transaction)
                        ->with('success', 'Payment completed successfully.');
                }
            } catch (\Exception $e) {
                Log::warning('Payment verification failed during redirect', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }

            // If verification fails or payment is still pending, show pending page
            return redirect()->route('alumni.payments.pending', $transaction)
                ->with('info', 'Your payment is being processed. Please wait while we confirm your payment.');

        } catch (\Exception $e) {
            Log::error('Payment redirect handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->route('alumni.payments.index')
                ->with('error', 'Failed to process payment redirect. Please contact support.');
        }
    }
}
