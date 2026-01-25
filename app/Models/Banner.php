<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'variant',
        'page',
        'start_date',
        'end_date',
        'status',
        'priority',
        'dismissible',
        'action_text',
        'action_url',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'dismissible' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the user who created the banner.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get banners for a specific page.
     */
    public function scopeForPage($query, $page)
    {
        return $query->where(function ($q) use ($page) {
            $q->where('page', 'all')
              ->orWhere('page', $page);
        });
    }

    /**
     * Scope to get banners within valid date range.
     */
    public function scopeWithinDateRange($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $now);
        });
    }

    /**
     * Scope to get displayable banners (active + within date range).
     */
    public function scopeDisplayable($query)
    {
        return $query->active()->withinDateRange();
    }
}
