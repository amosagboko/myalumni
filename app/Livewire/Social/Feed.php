<?php

namespace App\Livewire\Social;

use App\Livewire\Social\Concerns\ListensForSocialBroadcasts;
use App\Services\Social\FeedService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use ListensForSocialBroadcasts;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function getListeners(): array
    {
        return array_merge(
            [
                'post-created' => '$refresh',
                'comment-added' => '$refresh',
            ],
            $this->socialEchoListeners()
        );
    }

    public function render(FeedService $feedService)
    {
        return view('livewire.social.feed', [
            'posts' => $feedService->paginateFeed(Auth::user(), 10),
        ]);
    }
}
