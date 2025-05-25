<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Follow;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FollowSystem extends Component
{

    public $search = '';
    public $users = null;
    public $following;
    public $followers;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->users = collect();
        $this->loadFollowData();
    }

    public function loadFollowData()
    {
        $user = Auth::user();

        $this->following = User::whereIn(
            'id',
            Follow::where('follower_id', $user->id)->pluck('following_id')
        )->get();

        $this->followers = User::whereIn(
            'id',
            Follow::where('following_id', $user->id)->pluck('follower_id')
        )->get();
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 2) {
            $this->users = User::where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->where('id', '!=', Auth::id())
                ->get();
        } else {
            $this->users = collect(); // return empty collection
        }
    }

    public function follow($userId)
    {
        if ($userId != Auth::id() && !$this->isFollowing($userId)) {
            Follow::create([
                'follower_id' => Auth::id(),
                'following_id' => $userId
            ]);
            $this->loadFollowData();
            $this->dispatch('refreshComponent');
        }
    }

    public function unfollow($userId)
    {
        Follow::where('follower_id', Auth::id())
            ->where('following_id', $userId)
            ->delete();
        $this->loadFollowData();
        $this->dispatch('refreshComponent');
    }

    public function isFollowing($userId)
    {
        return $this->following->contains('id', $userId);
    }

    public function getFollowersCountProperty()
    {
        return $this->followers->count();
    }

    public function getFollowingCountProperty()
    {
        return $this->following->count();
    }

    public function render()
    {
        return view('livewire.follow-system')
            ->extends('layouts.alumni')
            ->section('content');
    }


}
