@php
    use App\Services\Alumni\ClearanceStatusService;

    $alumni = Auth::user()->alumni;
    $clearanceStatus = app(ClearanceStatusService::class)->snapshot(Auth::user(), $alumni);
    $requiresClearance = $clearanceStatus['requiresDivisionClearance'];
@endphp

<div class="col-xl-4 col-xxl-3 col-lg-4 ps-lg-0">
    @if($requiresClearance || $alumniNeedsBioData || $alumniNeedsPayments)
    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
        <div class="card-body p-4">
            <h4 class="fw-700 mb-3 font-xssss text-grey-900">Account Status</h4>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $alumniNeedsBioData ? 'bg-danger' : 'bg-success' }} font-xssss">Onboarding: {{ $alumniNeedsBioData ? 'Pending' : 'Done' }}</span>
                <span class="badge {{ $alumniNeedsPayments ? 'bg-danger' : 'bg-success' }} font-xssss">Payments: {{ $alumniNeedsPayments ? 'Pending' : 'Done' }}</span>
                @if($requiresClearance && $alumni)
                    <span class="badge {{ $clearanceStatus['studentAffairsCleared'] ? 'bg-success' : 'bg-danger' }} font-xssss">Student Affairs</span>
                    <span class="badge {{ $clearanceStatus['academicAffairsCleared'] ? 'bg-success' : 'bg-danger' }} font-xssss">Academic Affairs</span>
                @endif
            </div>
            @if($requiresClearance)
                <a href="{{ route('alumni.clearance-status') }}" class="btn btn-sm btn-outline-primary mt-3 font-xssss">View Clearance Status</a>
            @endif
        </div>
    </div>
    @endif

    <livewire:social.connection-requests />

    <livewire:social.suggested-connections />

    <livewire:social.feed-official-events-teaser />
</div>
