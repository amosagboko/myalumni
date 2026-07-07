<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\Transaction;
use App\Models\FeeTemplate;
use App\Services\CredoCentralService;
use App\Services\PaymentCompletionService;
use App\Services\AlumniDuesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class AlumniPaymentController extends Controller
{
    public function __construct(
        protected CredoCentralService $credocentral,
        protected PaymentCompletionService $paymentCompletion,
        protected AlumniDuesService $duesService,
    ) {
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $alumni = $user->alumni;

        $this->duesService->ensureAnnualDueAssigned($alumni);

        $fees = $alumni->getActiveFees();
        $duesPhase = $alumni->getDuesPhase();
        $activePaymentYear = \App\Models\AlumniYear::where('is_active', true)->first();

        return view('alumni.payments.index', compact('fees', 'duesPhase', 'activePaymentYear'));
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

            if ($fee->feeType->isEoiFee()) {
                return redirect()->back()->with(
                    'error',
                    'EOI screening fees must be paid through the Expression of Interest application flow.'
                );
            }

            if (!$this->duesService->feeIsPayableByAlumni($fee, $alumni)) {
                return redirect()->back()->with('error', 'This fee is not applicable to your account at this time.');
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
            Log::info('Payment webhook received', [
                'method' => $request->method(),
                'payload' => $request->all(),
                'reference' => $request->reference,
                'transRef' => $request->transRef,
                'status' => $request->status
            ]);

            // For GET requests (redirects), handle differently
            if ($request->isMethod('get')) {
                // Find the transaction by payment reference
                $transaction = Transaction::where('payment_reference', $request->reference)
                    ->orWhere('payment_provider_reference', $request->transRef)
                    ->first();

                if (!$transaction) {
                    Log::error('Transaction not found for webhook', [
                        'reference' => $request->reference,
                        'transRef' => $request->transRef
                    ]);
                    return redirect()->route('alumni.payments.index')
                        ->with('error', 'Transaction not found. Please contact support.');
                }

                if ($request->transRef) {
                    $transaction->update([
                        'payment_provider_reference' => $request->transRef,
                    ]);
                    $transaction->refresh();
                }

                // Always verify payment status through API regardless of redirect status
                try {
                    $result = $this->credocentral->verifyPayment(
                        $transaction,
                        $request->transRef ?: null
                    );

                    if ($result['paid']) {
                        $this->paymentCompletion->complete(
                            $transaction,
                            $result['paid_at'] ?? null,
                            [
                                'verified_at' => now()->toIso8601String(),
                                'verification_data' => $result,
                            ]
                        );

                        return redirect()->route('alumni.payments.success', $transaction)
                            ->with('success', 'Payment completed successfully.');
                    }

                    // If not paid, check if it's explicitly failed
                    if (strtolower($result['status']) === 'failed') {
                        $transaction->update([
                            'status' => 'failed',
                            'payment_details' => array_merge(
                                $transaction->payment_details ?? [],
                                [
                                    'status' => $result['status'],
                                    'failed_at' => now(),
                                    'verification_data' => $result
                                ]
                            )
                        ]);

                        return redirect()->route('alumni.payments.failed', $transaction)
                            ->with('error', 'Payment was not successful. Please try again.');
                    }

                    // If neither paid nor failed, show pending page
                    return redirect()->route('alumni.payments.pending', $transaction)
                        ->with(
                            'info',
                            'Your payment is still being confirmed by the payment provider. Please wait a minute, then click Verify Payment Status again.'
                        );

                } catch (\Exception $e) {
                    Log::warning('Payment verification failed during webhook redirect', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);

                    $transaction->update([
                        'payment_details' => array_merge(
                            is_array($transaction->payment_details) ? $transaction->payment_details : [],
                            [
                                'verification_error' => $e->getMessage(),
                                'redirect_handled_at' => now()->toIso8601String(),
                            ]
                        ),
                    ]);

                    return redirect()->route('alumni.payments.pending', $transaction)
                        ->with('warning', $this->technicalVerificationMessage($e));
                }
            }

            // For POST requests (webhooks), process normally
            $this->credocentral->handleWebhook($request->all());
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'method' => $request->method(),
                'request_data' => $request->all()
            ]);

            if ($request->isMethod('get')) {
                return redirect()->route('alumni.payments.index')
                    ->with('error', 'Failed to process payment. Please contact support.');
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request, Transaction $transaction)
    {
        $this->ensureAlumniOwnsTransaction($transaction);

        if ($transaction->isPaid()) {
            return redirect()->route('alumni.payments.success', $transaction);
        }

        try {
            Log::info('Starting manual payment verification', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->payment_reference,
                'provider_reference' => $transaction->payment_provider_reference,
                'current_status' => $transaction->status,
            ]);

            DB::beginTransaction();

            $transaction = Transaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $verification = $this->credocentral->verifyPayment($transaction);

            Log::info('Verification result received', [
                'transaction_id' => $transaction->id,
                'is_paid' => $verification['paid'],
                'status' => $verification['status'],
                'reference' => $transaction->payment_reference,
                'current_status' => $transaction->status,
            ]);

            if ($verification['paid']) {
                $this->paymentCompletion->complete(
                    $transaction,
                    $verification['paid_at'] ?? null,
                    [
                        'verified_at' => now()->toIso8601String(),
                        'verification_data' => $verification,
                        'manual_verification_at' => now()->toIso8601String(),
                    ]
                );

                DB::commit();

                return redirect()->route('alumni.payments.success', $transaction)
                    ->with('success', 'Payment verified successfully.');
            }

            if (strtolower($verification['status']) === 'failed') {
                $transaction->update([
                    'status' => 'failed',
                    'payment_details' => array_merge(
                        is_array($transaction->payment_details) ? $transaction->payment_details : [],
                        [
                            'status' => $verification['status'],
                            'failed_at' => now()->toIso8601String(),
                            'verification_data' => $verification,
                            'manual_verification_at' => now()->toIso8601String(),
                        ]
                    ),
                ]);

                DB::commit();

                return redirect()->route('alumni.payments.failed', $transaction)
                    ->with('error', 'Your payment was not successful. You can try again from the payments page.');
            }

            $transaction->update([
                'payment_details' => array_merge(
                    is_array($transaction->payment_details) ? $transaction->payment_details : [],
                    [
                        'verification_data' => $verification,
                        'manual_verification_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            DB::commit();

            return redirect()->route('alumni.payments.pending', $transaction)
                ->with(
                    'info',
                    'Your payment is still being confirmed by the payment provider. Please wait a minute, then click Verify Payment Status again.'
                );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment verification failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $existingDetails = is_array($transaction->payment_details) ? $transaction->payment_details : [];
            $transaction->update([
                'payment_details' => array_merge($existingDetails, [
                    'verification_error' => $e->getMessage(),
                    'manual_verification_at' => now()->toIso8601String(),
                ]),
            ]);

            return redirect()->route('alumni.payments.pending', $transaction)
                ->with('warning', $this->technicalVerificationMessage($e));
        }
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess(Transaction $transaction)
    {
        $transaction->loadMissing('feeTemplate.feeType');

        $eoiApplication = null;
        if ($this->paymentCompletion->isEoiTransaction($transaction)) {
            $meta = $this->paymentCompletion->resolveEoiMetadata($transaction);
            $candidate = null;

            if (!empty($meta['candidate_id'])) {
                $candidate = \App\Models\Candidate::with(['office', 'election'])
                    ->find($meta['candidate_id']);
            }

            $eoiApplication = [
                'status_label' => $candidate?->status_label ?? 'Paid, awaiting ELCOM screening',
                'office' => $candidate?->office?->title,
                'election' => $candidate?->election?->title,
            ];
        }

        return view('payments.success', compact('transaction', 'eoiApplication'));
    }

    /**
     * Show payment pending page
     */
    public function paymentPending(Transaction $transaction)
    {
        $this->ensureAlumniOwnsTransaction($transaction);

        if ($transaction->isPaid()) {
            return redirect()->route('alumni.payments.success', $transaction);
        }

        if ($transaction->status === 'failed') {
            return redirect()->route('alumni.payments.failed', $transaction);
        }

        $transaction->loadMissing('feeTemplate.feeType');

        return view('payments.pending', compact('transaction'));
    }

    /**
     * Show payment failed page
     */
    public function paymentFailed(Transaction $transaction)
    {
        if ($transaction->isPaid()) {
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
            if ($transaction->isPaid()) {
                return redirect()->route($redirectRoute)
                    ->with('info', 'This payment has already been completed.');
            }

            $this->paymentCompletion->complete($transaction, now()->toIso8601String(), [
                'demo_payment' => true,
                'completed_at' => now()->toIso8601String(),
            ]);

            $successMessage = $this->paymentCompletion->isEoiTransaction($transaction)
                ? 'Payment completed successfully. Your expression of interest has been submitted.'
                : 'Demo payment completed successfully.';

            return redirect()->route($redirectRoute)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Failed to process demo payment', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

        $baseQuery = Transaction::query()->where('alumni_id', $alumni->id);

        $transactions = (clone $baseQuery)
            ->with(['feeTemplate.feeType'])
            ->latest()
            ->paginate(10);

        $summary = [
            'paid_count' => (clone $baseQuery)->paid()->count(),
            'pending_count' => (clone $baseQuery)->pending()->count(),
            'total_paid' => (clone $baseQuery)->paid()->sum('amount'),
        ];

        return view('alumni.payments.history', compact('transactions', 'summary'));
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

        try {
            // Initialize payment with Credo Central and get the payment link
            $paymentLink = $this->credocentral->initializePayment($transaction);
            return redirect()->away($paymentLink);
        } catch (\Exception $e) {
            Log::error('Failed to initialize payment with Credo Central (processPayment)', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_reference' => $transaction->payment_reference,
                'amount' => $transaction->amount
            ]);
            return redirect()->route('alumni.payments.show', $transaction)
                ->with('error', 'Failed to initiate payment. Please try again.');
        }
    }

    /**
     * Handle payment redirect after Credo Central checkout.
     */
    public function handleRedirect(Request $request)
    {
        try {
            Log::info('Received payment redirect', [
                'reference' => $request->reference,
                'transRef' => $request->transRef,
                'status' => $request->status,
                'params' => $request->all(),
            ]);

            DB::beginTransaction();
            try {
                $transaction = Transaction::where('payment_reference', $request->reference)
                    ->orWhere('payment_provider_reference', $request->transRef)
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    DB::rollBack();
                    return redirect()->route('alumni.payments.index')
                        ->with('error', 'Transaction not found. Please contact support.');
                }

                if ($request->transRef) {
                    $transaction->update([
                        'payment_provider_reference' => $request->transRef,
                    ]);
                    $transaction->refresh();
                }

                if ($transaction->isPaid()) {
                    DB::commit();
                    return redirect()->route('alumni.payments.success', $transaction)
                        ->with('success', 'Payment completed successfully.');
                }

                try {
                    $verification = $this->credocentral->verifyPayment(
                        $transaction,
                        $request->transRef ?: null
                    );

                    if ($verification['paid']) {
                        $this->paymentCompletion->complete(
                            $transaction,
                            $verification['paid_at'] ?? null,
                            [
                                'verified_at' => now()->toIso8601String(),
                                'verification_data' => $verification,
                                'redirect_status' => $request->status,
                                'redirect_handled_at' => now()->toIso8601String(),
                            ]
                        );

                        DB::commit();
                        return redirect()->route('alumni.payments.success', $transaction)
                            ->with('success', 'Payment completed successfully.');
                    }

                    if (strtolower($verification['status']) === 'failed') {
                        $transaction->update([
                            'status' => 'failed',
                            'payment_details' => array_merge(
                                is_array($transaction->payment_details) ? $transaction->payment_details : [],
                                [
                                    'status' => $verification['status'],
                                    'failed_at' => now()->toIso8601String(),
                                    'verification_data' => $verification,
                                    'redirect_handled_at' => now()->toIso8601String(),
                                ]
                            ),
                        ]);

                        DB::commit();
                        return redirect()->route('alumni.payments.failed', $transaction)
                            ->with('error', 'Payment was not successful. Please try again.');
                    }

                    $transaction->update([
                        'payment_details' => array_merge(
                            is_array($transaction->payment_details) ? $transaction->payment_details : [],
                            [
                                'verification_data' => $verification,
                                'redirect_status' => $request->status,
                                'redirect_handled_at' => now()->toIso8601String(),
                            ]
                        ),
                    ]);

                    DB::commit();
                    return redirect()->route('alumni.payments.pending', $transaction)
                        ->with(
                            'info',
                            'Your payment is still being confirmed by the payment provider. Please wait a minute, then click Verify Payment Status again.'
                        );

                } catch (\Exception $e) {
                    Log::error('Payment verification failed on redirect', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);

                    $transaction->update([
                        'payment_details' => array_merge(
                            is_array($transaction->payment_details) ? $transaction->payment_details : [],
                            [
                                'verification_error' => $e->getMessage(),
                                'redirect_status' => $request->status,
                                'redirect_handled_at' => now()->toIso8601String(),
                            ]
                        ),
                    ]);

                    DB::commit();
                    return redirect()->route('alumni.payments.pending', $transaction)
                        ->with('warning', $this->technicalVerificationMessage($e));
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error while handling payment redirect', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->route('alumni.payments.index')
                ->with('error', 'Failed to handle payment response. Please contact support.');
        }
    }

    protected function ensureAlumniOwnsTransaction(Transaction $transaction): void
    {
        $alumniId = Auth::user()?->alumni?->id;

        if (!$alumniId || $transaction->alumni_id !== $alumniId) {
            Log::warning('Unauthorized access attempt to transaction', [
                'transaction_id' => $transaction->id,
                'alumni_id' => $transaction->alumni_id,
                'user_id' => Auth::id(),
            ]);
            abort(403, 'You are not authorized to view this transaction.');
        }
    }

    protected function technicalVerificationMessage(\Throwable $e): string
    {
        if (str_contains($e->getMessage(), 'No Credo transaction reference')) {
            return 'We are still linking your payment to our system. If money was deducted from your account, please wait a minute and click Verify Payment Status again. If this continues, contact support with your payment reference below.';
        }

        if ($e instanceof \Illuminate\Http\Client\ConnectionException
            || str_contains(strtolower($e->getMessage()), 'connect')) {
            return 'We could not reach the payment provider right now. Please check your connection, wait a minute, and click Verify Payment Status again.';
        }

        return 'We could not confirm your payment right now. If money was deducted, wait a minute and click Verify Payment Status again, or contact support with your payment reference below.';
    }
}
