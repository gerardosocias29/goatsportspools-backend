<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquaresPoolWinner extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'square_id',
        'player_id',
        'quarter',
        'prize_amount',
        'home_score',
        'visitor_score',
    ];

    protected $casts = [
        'quarter' => 'integer',
        'prize_amount' => 'decimal:2',
        'home_score' => 'integer',
        'visitor_score' => 'integer',
    ];

    /**
     * Get the pool
     */
    public function pool()
    {
        return $this->belongsTo(SquaresPool::class, 'pool_id');
    }

    /**
     * Get the square
     */
    public function square()
    {
        return $this->belongsTo(SquaresPoolSquare::class, 'square_id');
    }

    /**
     * Get the player
     */
    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }
}
