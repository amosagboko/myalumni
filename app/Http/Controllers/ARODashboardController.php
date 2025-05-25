<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Alumni;
use App\Models\Post;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ARODashboardController extends Controller
{
    public function index()
    {
        // Get total alumni count
        $totalAlumni = User::whereHas('roles', function ($query) {
            $query->where('name', 'alumni');
        })->count();

        // Get active events count (events that haven't ended yet)
        $activeEvents = Event::where('date', '>=', now())->count();

        // Get pending posts count (posts that need moderation)
        $pendingPosts = Post::where('status', 'pending')->count();

        // Get new messages count (unread messages)
        $newMessages = Message::where('receiver_id', Auth::id())
            ->where('read_at', null)
            ->count();

        // Get upcoming events (next 5)
        $upcomingEvents = Event::where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        return view('aro', compact(
            'totalAlumni',
            'activeEvents',
            'pendingPosts',
            'newMessages',
            'upcomingEvents'
        ));
    }
} 