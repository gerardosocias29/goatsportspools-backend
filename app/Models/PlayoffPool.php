<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PlayoffPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'playoff_id',
        'pool_number',
        'password',
        'pool_name',
        'pool_description',
        'initial_credits',
        'credit_cost_per_bracket',
        'max_brackets_per_user',
        'close_datetime',
        'locked_at',
        'pool_status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'close_datetime' => 'datetime',
        'locked_at' => 'datetime',
    ];

    /**
     * Generate a unique 6-char pool number (reuses SquaresPool pattern).
     */
    public static function generatePoolNumber()
    {
        do {
            $poolNumber = strtoupper(Str::random(6));
        } while (self::where('pool_number', $poolNumber)->exists());
        return $poolNumber;
    }

    /**
     * Whether the pool is locked (either explicitly or past close time).
     */
    public function getIsLockedAttribute()
    {
        if ($this->locked_at) return true;
        if ($this->close_datetime && $this->close_datetime->isPast()) return true;
        return false;
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function playoff()
    {
        return $this->belongsTo(NbaPlayoff::class, 'playoff_id');
    }

    public function participants()
    {
        return $this->hasMany(PlayoffPoolParticipant::class, 'pool_id');
    }
}
