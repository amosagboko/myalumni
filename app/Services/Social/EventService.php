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
            ->ordered()
            ->where('date', '>=', now()->toDateString());
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

    public function teaser(int $limit = 3)
    {
        return $this->upcomingQuery()->limit($limit)->get();
    }

    public function isVisibleToAlumni(Event $event): bool
    {
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
            'connect' => 'Connect',
            'event' => 'News',
            'opportunity' => 'Official Event',
            default => 'Official Event',
        };
    }
}
