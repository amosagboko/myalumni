@if(Auth::user()->hasRole('alumni'))
    @php
        $alumni = Auth::user()->alumni;
        $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
        $activeFees = $alumni ? $alumni->getActiveFees() : collect([]);
        $unpaidFees = $activeFees->filter(fn ($fee) => !$fee->isPaid());
        $needsPayments = $alumni && $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
        $activePaymentYear = \App\Models\AlumniYear::where('is_active', true)->first();
        $duesPhase = $alumni ? $alumni->getDuesPhase() : 'none';
    @endphp

    @if($needsBioData || $needsPayments)
    <div class="modal fade show" id="onboardingModal" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Your Profile</h5>
                </div>
                <div class="modal-body">
                    @if($needsBioData)
                        <div class="mb-4">
                            <h6>Bio Data Required</h6>
                            <p>Please complete your bio data to continue using the platform.</p>
                            <a href="{{ route('alumni.bio-data') }}" class="btn btn-primary">Complete Bio Data</a>
                        </div>
                    @endif

                    @if($needsPayments)
                        <div class="mb-4">
                            <h6>Pending Payments</h6>
                            @if($duesPhase === 'annual' && $activePaymentYear)
                                <p class="mb-2">
                                    Your <strong>annual alumni due for payment year {{ $activePaymentYear->year }}</strong> is unpaid.
                                </p>
                            @else
                                <p class="mb-2">You have the following pending payments that need to be completed:</p>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Fee</th>
                                            @if($duesPhase === 'annual')
                                                <th>Payment year</th>
                                            @endif
                                            <th>Amount</th>
                                            @if($duesPhase === 'annual')
                                                <th>Valid period</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unpaidFees as $fee)
                                            <tr>
                                                <td>
                                                    @if($duesPhase === 'annual')
                                                        {{ $fee->description ?: $fee->feeType->name }}
                                                    @else
                                                        {{ $fee->displayLabel($activePaymentYear) }}
                                                    @endif
                                                </td>
                                                @if($duesPhase === 'annual')
                                                    <td><strong>{{ $fee->paymentYearLabel($activePaymentYear) ?? '—' }}</strong></td>
                                                @endif
                                                <td>₦{{ number_format($fee->amount, 2) }}</td>
                                                @if($duesPhase === 'annual')
                                                    <td>{{ $fee->validPeriodLabel() ?? '—' }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('alumni.payments.index') }}" class="btn btn-primary">View and Pay Fees</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
@endif
