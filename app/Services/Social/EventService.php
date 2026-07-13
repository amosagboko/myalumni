<?php

namespace App\Services\Social;

use App\Models\Event;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class EventService
{
    public function upcomingQuery(): Builder
    {
        return Event::query()
            ->published()
            ->where(function (Builder $query) {
                $query->whereNull('date')
                    ->orWhere('date', '>=', now()->toDateString());
            })
            ->orderedForAlumniDisplay();
    }

    public function upcomingByTypeQuery(string $type): Builder
    {
        return $this->upcomingQuery()->ofType($type);
    }

    public function teaser(int $limit = 3)
    {
        return $this->upcomingQuery()->limit(max(1, $limit))->get();
    }

    public function teaserByType(string $type, int $limit = 3)
    {
        return $this->upcomingByTypeQuery($type)->limit(max(1, $limit))->get();
    }

    /**
     * Alumni home strip: the three most recently created published items of a type.
     * Ordered oldest-to-newest within the trio for slideshow progression.
     */
    public function stripCarouselByType(string $type, int $limit = 3)
    {
        $limit = max(1, min(3, $limit));

        $items = Event::query()
            ->published()
            ->ofType($type)
            ->where(function (Builder $query) {
                $query->whereNull('date')
                    ->orWhere('date', '>=', now()->toDateString());
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $items
            ->sortBy(fn (Event $event) => [$event->created_at?->timestamp ?? 0, $event->id])
            ->values();
    }

    public function pastQuery(): Builder
    {
        return Event::query()
            ->published()
            ->ordered()
            ->where('date', '<', now()->toDateString());
    }

    public function allPublishedQuery(): Builder
    {
        return Event::query()->published()->ordered();
    }

    public function paginate(string $filter = 'upcoming', int $perPage = 9): LengthAwarePaginator
    {
        $query = match ($filter) {
            'past' => $this->pastQuery(),
            'all' => $this->allPublishedQuery(),
            default => $this->upcomingQuery(),
        };

        return $query->paginate($perPage);
    }

    public function paginateByType(string $type, string $filter = 'upcoming', int $perPage = 9): LengthAwarePaginator
    {
        $query = match ($filter) {
            'past' => $this->pastQuery()->ofType($type),
            'all' => $this->allPublishedQuery()->ofType($type),
            default => $this->upcomingByTypeQuery($type),
        };

        return $query->paginate($perPage);
    }

    public function typeForDiscoverTab(string $tab): string
    {
        return match ($tab) {
            'highlights' => 'connect',
            'news' => 'event',
            default => 'opportunity',
        };
    }

    public function discoverTabForType(?string $type): string
    {
        return match ($type) {
            'connect' => 'highlights',
            'event' => 'news',
            default => 'events',
        };
    }

    public function discoverTabLabel(string $tab): string
    {
        return match ($tab) {
            'highlights' => 'Highlights',
            'news' => 'News',
            default => 'Events',
        };
    }

    public function isVisibleToAlumni(Event $event): bool
    {
        if (! Schema::hasColumn('events', 'is_published')) {
            return true;
        }

        return (bool) $event->is_published;
    }

    public function shareCount(Event $event): int
    {
        if (! Schema::hasColumn('posts', 'event_id')) {
            return 0;
        }

        return Post::query()
            ->where('event_id', $event->id)
            ->where('status', 'published')
            ->count();
    }

    public function typeLabel(?string $type): string
    {
        return match ($type) {
            'connect' => 'Highlights',
            'event' => 'News',
            'opportunity' => 'Events',
            default => 'Events',
        };
    }
}
