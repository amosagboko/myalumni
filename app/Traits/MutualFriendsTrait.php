<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Livewire\FriendRequestManager;

trait MutualFriendsTrait
{
    /**
     * Check if the content can be viewed by the given user
     */
    public function canBeViewedBy(User $user)
    {
        // User can always view their own content
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check if users are mutual friends
        $senderId = $this->user_id;
        $receiverId = $user->id;

        $mutualFriendship = DB::table('friend_requests')
            ->where(function($query) use ($senderId, $receiverId) {
                $query->where(function($q) use ($senderId, $receiverId) {
                    $q->where('sender_id', $senderId)
                      ->where('receiver_id', $receiverId)
                      ->where('status', 'accepted');
                })
                ->orWhere(function($q) use ($senderId, $receiverId) {
                    $q->where('sender_id', $receiverId)
                      ->where('receiver_id', $senderId)
                      ->where('status', 'accepted');
                });
            })
            ->exists();

        return $mutualFriendship;
    }

    /**
     * Scope to get only posts/comments visible to the given user
     */
    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            // User's own content
            $q->where('user_id', $user->id)
              // Or content from mutual friends
              ->orWhereHas('user', function($q) use ($user) {
                  $q->where(function($q) use ($user) {
                      $q->whereRaw('EXISTS (
                          SELECT 1 FROM friend_requests fr 
                          WHERE ((fr.sender_id = users.id AND fr.receiver_id = ?) 
                          OR (fr.sender_id = ? AND fr.receiver_id = users.id))
                          AND fr.status = "accepted"
                      )', [$user->id, $user->id]);
                  });
              });
        });
    }
} 