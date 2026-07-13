<div class="card w-100 shadow-none bg-transparent bg-transparent-card border-0 p-0 mb-0">
    @php
        $welcomeUser = Auth::user();
        $welcomeAlumni = $welcomeUser->alumni;
        $welcomeAvatar = $welcomeUser->avatar
            ? asset('storage/' . $welcomeUser->avatar)
            : asset('/images/user-8.png');
        $welcomeClassYear = $welcomeAlumni?->year_of_graduation;
    @endphp
    <div class="feed-announcements-row" aria-label="Welcome, highlights, news, and events">
        <div class="feed-announcements-welcome mb-3 mt-3">
            <a href="{{ route('alumni.members.show', $welcomeUser) }}"
               class="text-decoration-none d-block feed-announcements-welcome__link"
               aria-label="View your profile">
                <div class="card w125 h200 d-block border-0 shadow-xss rounded-xxxl overflow-hidden mb-0 feed-announcement-event-slide__card feed-announcements-welcome__card">
                    <div class="feed-announcements-welcome__media">
                        <img src="{{ $welcomeAvatar }}"
                             alt="{{ $welcomeUser->name }}"
                             class="feed-announcements-welcome__avatar-img">
                    </div>
                    <div class="feed-announcements-welcome__meta text-center">
                        <h4 class="fw-700 ls-1 font-xssss text-white mb-0">{{ \Illuminate\Support\Str::limit($welcomeUser->name, 16) }}</h4>
                        @if($welcomeClassYear)
                            <span class="d-block font-xsssss text-white opacity-75 mt-1">Class of {{ $welcomeClassYear }}</span>
                        @else
                            <span class="d-block font-xsssss text-white opacity-75 mt-1">View profile</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>

        <div class="feed-announcements-carousel-wrap mb-3 mt-3">
            <p class="feed-announcements-carousel-label mb-1">Highlights</p>
            @include('alumni.partials.feed-event-carousel', [
                'items' => $highlightItems,
                'carouselId' => 'feedAnnouncementsHighlightsCarousel',
                'variant' => 'announcement',
                'emptyMessage' => 'No highlights yet.',
            ])
        </div>

        <div class="feed-announcements-carousel-wrap mb-3 mt-3">
            <p class="feed-announcements-carousel-label mb-1">News</p>
            @include('alumni.partials.feed-event-carousel', [
                'items' => $newsItems,
                'carouselId' => 'feedAnnouncementsNewsCarousel',
                'variant' => 'announcement',
                'emptyMessage' => 'No news yet.',
            ])
        </div>

        <div class="feed-announcements-carousel-wrap mb-3 mt-3">
            <p class="feed-announcements-carousel-label mb-1">Events</p>
            @include('alumni.partials.feed-event-carousel', [
                'items' => $eventItems,
                'carouselId' => 'feedAnnouncementsEventsCarousel',
                'variant' => 'announcement',
                'emptyMessage' => 'No events yet.',
            ])
        </div>
    </div>
</div>
