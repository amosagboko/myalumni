<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LimitedAlumniRecord extends Model
{
    public const DEFAULT_GRADUATION_YEAR = 2026;

    protected $fillable = [
        'matric_number',
        'firstname',
        'surname',
        'year_of_graduation',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'year_of_graduation' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fullName(): string
    {
        return trim($this->firstname.' '.$this->surname);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function markCompleted(): void
    {
        $this->forceFill(['completed_at' => now()])->save();
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeForMatric(Builder $query, string $matric): Builder
    {
        return $query->where('matric_number', trim($matric));
    }
}
