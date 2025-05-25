<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class FriendRequest extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            Log::info('Creating friend request', [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'status' => $request->status
            ]);
        });

        static::updating(function ($request) {
            Log::info('Updating friend request', [
                'request_id' => $request->id,
                'old_status' => $request->getOriginal('status'),
                'new_status' => $request->status
            ]);
        });
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Create a new friend request
     */
    public static function createRequest($senderId, $receiverId)
    {
        // Check if a request already exists
        $existingRequest = self::where(function($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->first();

        if ($existingRequest) {
            Log::info('Friend request already exists', [
                'request_id' => $existingRequest->id,
                'status' => $existingRequest->status
            ]);
            return $existingRequest;
        }

        // Create new request
        $request = self::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);

        Log::info('New friend request created', [
            'request_id' => $request->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => 'pending'
        ]);

        return $request;
    }

    /**
     * Accept a friend request
     */
    public function accept()
    {
        if ($this->status !== 'pending') {
            Log::warning('Cannot accept non-pending friend request', [
                'request_id' => $this->id,
                'current_status' => $this->status
            ]);
            return false;
        }

        $this->update(['status' => 'accepted']);
        Log::info('Friend request accepted', [
            'request_id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id
        ]);
        return true;
    }

    /**
     * Reject a friend request
     */
    public function reject()
    {
        if ($this->status !== 'pending') {
            Log::warning('Cannot reject non-pending friend request', [
                'request_id' => $this->id,
                'current_status' => $this->status
            ]);
            return false;
        }

        $this->update(['status' => 'rejected']);
        Log::info('Friend request rejected', [
            'request_id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id
        ]);
        return true;
    }

    /**
     * Check if two users are friends
     */
    public static function areFriends($userId1, $userId2)
    {
        return self::where(function($query) use ($userId1, $userId2) {
            $query->where(function($q) use ($userId1, $userId2) {
                $q->where('sender_id', $userId1)
                  ->where('receiver_id', $userId2);
            })
            ->orWhere(function($q) use ($userId1, $userId2) {
                $q->where('sender_id', $userId2)
                  ->where('receiver_id', $userId1);
            });
        })
        ->where('status', 'accepted')
        ->exists();
    }

    /**
     * Get pending friend requests for a user
     */
    public static function getPendingRequests($userId)
    {
        return self::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->get();
    }

    /**
     * Get accepted friends for a user
     */
    public static function getAcceptedFriends($userId)
    {
        return self::where(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })
        ->where('status', 'accepted')
        ->with(['sender', 'receiver'])
        ->get();
    }
}
