@php
    use App\Models\Event;
    $upcomingEvents = collect();
    try {
        $upcomingEvents = Event::published()->ordered()->where('date', '>=', now()->toDateString())->limit(5)->get();
    } catch (\Throwable $e) {
        report($e);
    }
@endphp

<div class="card w-100 shadow-none bg-transparent bg-transparent-card border-0 p-0 mb-0">
    <div class="owl-carousel category-card owl-theme overflow-hidden nav-none">
        <div class="item">
            <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl bg-gradiant-bottom overflow-hidden mb-3 mt-3" style="background: linear-gradient(135deg, #132977 0%, #05f 100%);">
                <div class="card-body d-block p-3 w-100 position-absolute bottom-0 text-center">
                    <span class="btn-round-lg bg-white d-inline-flex align-items-center justify-content-center"><i class="feather-zap font-lg text-primary"></i></span>
                    <div class="clearfix"></div>
                    <h4 class="fw-700 position-relative z-index-1 ls-1 font-xssss text-white mt-2 mb-1">Welcome, {{ Auth::user()->name }}</h4>
                </div>
            </div>
        </div>
        @forelse($upcomingEvents as $event)
            <div class="item">
                <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl bg-gradiant-bottom overflow-hidden cursor-pointer mb-3 mt-3"
                     @if($event->image) style="background-image: url('{{ asset('storage/' . $event->image) }}'); background-size: cover; background-position: center;" @else style="background: linear-gradient(135deg, #10d876 0%, #132977 100%);" @endif>
                    <div class="card-body d-block p-3 w-100 position-absolute bottom-0 text-center">
                        <div class="clearfix"></div>
                        <h4 class="fw-600 position-relative z-index-1 ls-1 font-xssss text-white mt-2 mb-1">{{ \Illuminate\Support\Str::limit($event->eventname, 28) }}</h4>
                        <span class="d-block font-xsssss text-white opacity-75">{{ $event->date?->format('M j') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="item">
                <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl bg-greylight overflow-hidden mb-3 mt-3">
                    <div class="card-body d-flex align-items-center justify-content-center h-100 p-3 text-center">
                        <p class="font-xssss text-grey-500 mb-0">Official events will appear here.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
