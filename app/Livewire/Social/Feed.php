<?php

namespace App\Livewire\Social;

use App\Services\Social\FeedService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Feed extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['post-created' => '$refresh', 'comment-added' => '$refresh'];

    public function render(FeedService $feedService)
    {
        return view('livewire.social.feed', [
            'posts' => $feedService->paginateFeed(Auth::user(), 10),
        ]);
    }
}
