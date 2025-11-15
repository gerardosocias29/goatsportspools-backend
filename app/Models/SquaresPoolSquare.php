<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquaresPoolSquare extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'x_coordinate',
        'y_coordinate',
        'x_number',
        'y_number',
        'player_id',
        'claimed_at',
    ];

    protected $casts = [
        'x_coordinate' => 'integer',
        'y_coordinate' => 'integer',
        'x_number' => 'integer',
        'y_number' => 'integer',
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the pool for this square
     */
    public function pool()
    {
        return $this->belongsTo(SquaresPool::class, 'pool_id');
    }

    /**
     * Get the player who owns this square
     */
    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /**
     * Check if this square is claimed
     */
    public function isClaimedAttribute()
    {
        return !is_null($this->player_id);
    }
}
