<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'content',
        'type',
        'status',
        'metadata',
        'file_path'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($report) {
            $report->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('F j, Y');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('F j, Y');
    }
} 