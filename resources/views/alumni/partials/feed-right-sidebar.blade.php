@php
    use App\Models\Event;

    $upcomingEvents = collect();

    try {
        $upcomingEvents = Event::published()->ordered()->where('date', '>=', now()->toDateString())->limit(3)->get();
    } catch (\Throwable $e) {
        report($e);
    }

    $alumni = Auth::user()->alumni;
    $yearOfGraduation = $alumni?->year_of_graduation;
    $requiresClearance = $yearOfGraduation && $yearOfGraduation >= 2025;
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
                    @php
                        $studentCleared = (bool) ($alumni->student_affairs_cleared ?? false);
                        $academicCleared = (bool) ($alumni->academic_affairs_cleared ?? false);
                    @endphp
                    <span class="badge {{ $studentCleared ? 'bg-success' : 'bg-danger' }} font-xssss">Student Affairs</span>
                    <span class="badge {{ $academicCleared ? 'bg-success' : 'bg-danger' }} font-xssss">Academic Affairs</span>
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

    <div class="card w-100 shadow-xss rounded-xxl border-0 mb-3">
        <div class="card-body d-flex align-items-center p-4">
            <h4 class="fw-700 mb-0 font-xssss text-grey-900">Official Events</h4>
            <a href="#" class="fw-600 ms-auto font-xssss text-primary">See all</a>
        </div>
        @forelse($upcomingEvents as $event)
            <div class="card-body d-flex pt-0 ps-4 pe-4 pb-3 overflow-hidden {{ $loop->first ? 'border-top-xs bor-0' : '' }}">
                @php
                    $month = $event->date?->format('M');
                    $day = $event->date?->format('j');
                    $badgeClass = ['bg-success', 'bg-warning', 'bg-primary'][$loop->index % 3];
                @endphp
                <div class="{{ $badgeClass }} me-2 p-3 rounded-xxl">
                    <h4 class="fw-700 font-lg ls-3 lh-1 text-white mb-0">
                        <span class="ls-1 d-block font-xsss text-white fw-600">{{ strtoupper($month) }}</span>{{ $day }}
                    </h4>
                </div>
                <h4 class="fw-700 text-grey-900 font-xssss mt-2">
                    {{ $event->eventname }}
                    <span class="d-block font-xsssss fw-500 mt-1 lh-4 text-grey-500">{{ $event->venue ?? 'Venue TBA' }}</span>
                </h4>
            </div>
        @empty
            <div class="card-body pt-0 ps-4 pe-4 pb-4 border-top-xs bor-0">
                <p class="font-xssss text-grey-500 mb-0">No upcoming official events.</p>
            </div>
        @endforelse
    </div>
</div>
