<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingSetting extends Model
{
    protected $fillable = [
        'is_onboarding_enabled',
        'closure_reason',
        'closed_at',
        'reopened_at',
        'closed_by',
        'reopened_by'
    ];

    protected $casts = [
        'is_onboarding_enabled' => 'boolean',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime'
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    /**
     * Get the current onboarding setting or create a default one
     */
    public static function getCurrent(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'is_onboarding_enabled' => true,
                'closure_reason' => null,
                'closed_at' => null,
                'reopened_at' => null,
                'closed_by' => null,
                'reopened_by' => null
            ]
        );
    }

    /**
     * Check if onboarding is currently enabled
     */
    public static function isEnabled(): bool
    {
        return static::getCurrent()->is_onboarding_enabled;
    }

    /**
     * Close onboarding with a reason
     */
    public static function close(string $reason, int $userId): bool
    {
        $setting = static::getCurrent();
        $setting->update([
            'is_onboarding_enabled' => false,
            'closure_reason' => $reason,
            'closed_at' => now(),
            'closed_by' => $userId
        ]);

        return true;
    }

    /**
     * Reopen onboarding
     */
    public static function reopen(int $userId): bool
    {
        $setting = static::getCurrent();
        $setting->update([
            'is_onboarding_enabled' => true,
            'closure_reason' => null,
            'reopened_at' => now(),
            'reopened_by' => $userId
        ]);

        return true;
    }
} 