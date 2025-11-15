<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquaresPoolPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'player_id',
        'credits_available',
        'squares_count',
        'joined_at',
    ];

    protected $casts = [
        'credits_available' => 'integer',
        'squares_count' => 'integer',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the pool
     */
    public function pool()
    {
        return $this->belongsTo(SquaresPool::class, 'pool_id');
    }

    /**
     * Get the player
     */
    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /**
     * Get all squares claimed by this player in this pool
     */
    public function squares()
    {
        return SquaresPoolSquare::where('pool_id', $this->pool_id)
            ->where('player_id', $this->player_id)
            ->get();
    }
}
