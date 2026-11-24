<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceLog extends Model
{
    protected $fillable = [
        'alumni_id',
        'division',
        'actor_user_id',
        'actor_role',
        'old_value',
        'new_value',
        'reason',
    ];

    protected $casts = [
        'old_value' => 'boolean',
        'new_value' => 'boolean',
    ];

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function recordStudentAffairs(Alumni $alumni, User $actor, bool $newValue, string $reason): void
    {
        $old = (bool) $alumni->student_affairs_cleared;
        $alumni->student_affairs_cleared = $newValue;
        $alumni->save();

        static::query()->create([
            'alumni_id' => $alumni->id,
            'division' => 'student_affairs',
            'actor_user_id' => $actor->id,
            'actor_role' => $actor->getRoleNames()->first() ?? 'student-affairs',
            'old_value' => $old,
            'new_value' => $newValue,
            'reason' => $reason,
        ]);
    }
}
