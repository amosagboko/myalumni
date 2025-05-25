<?php

namespace App\Console\Commands;

use App\Models\FriendRequest;
use Illuminate\Console\Command;

class FixFriendRequests extends Command
{
    protected $signature = 'friend-requests:fix';
    protected $description = 'Fix friend request statuses';

    public function handle()
    {
        $this->info('Checking friend requests...');

        // Get all friend requests
        $requests = FriendRequest::all();
        $this->info("Found {$requests->count()} friend requests");

        foreach ($requests as $request) {
            $this->info("Checking request ID: {$request->id}");
            $this->info("Status: {$request->status}");
            $this->info("Sender: {$request->sender_id}");
            $this->info("Receiver: {$request->receiver_id}");
            $this->info("Created at: {$request->created_at}");
            $this->info("Updated at: {$request->updated_at}");
            $this->info("-------------------");
        }

        // Ask if user wants to fix the requests
        if ($this->confirm('Do you want to fix the friend requests?')) {
            // Reset all friend requests to pending
            FriendRequest::where('status', '!=', 'pending')->update(['status' => 'pending']);
            $this->info('All friend requests have been reset to pending status');
        }
    }
} 