<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SquaresPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'game_id',
        'pool_number',
        'password',
        'pool_name',
        'pool_type',
        'player_pool_type',
        'home_team_id',
        'visitor_team_id',
        'x_numbers',
        'y_numbers',
        'numbers_assigned',
        'entry_fee',
        'max_squares_per_player',
        'credit_cost',
        'initial_credits',
        'close_datetime',
        'number_assign_datetime',
        'pool_status',
        'qr_code_url',
        'reward1_percent',
        'reward2_percent',
        'reward3_percent',
        'reward4_percent',
    ];

    protected $casts = [
        'x_numbers' => 'array',
        'y_numbers' => 'array',
        'numbers_assigned' => 'boolean',
        'entry_fee' => 'decimal:2',
        'reward1_percent' => 'decimal:2',
        'reward2_percent' => 'decimal:2',
        'reward3_percent' => 'decimal:2',
        'reward4_percent' => 'decimal:2',
        'close_datetime' => 'datetime',
        'number_assign_datetime' => 'datetime',
    ];

    /**
     * Generate a unique pool number
     */
    public static function generatePoolNumber()
    {
        do {
            $poolNumber = strtoupper(Str::random(6));
        } while (self::where('pool_number', $poolNumber)->exists());

        return $poolNumber;
    }

    /**
     * Get the admin who created this pool
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the game for this pool
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the home team
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the visitor team
     */
    public function visitorTeam()
    {
        return $this->belongsTo(Team::class, 'visitor_team_id');
    }

    /**
     * Get all squares for this pool
     */
    public function squares()
    {
        return $this->hasMany(SquaresPoolSquare::class, 'pool_id');
    }

    /**
     * Get all players for this pool
     */
    public function players()
    {
        return $this->hasMany(SquaresPoolPlayer::class, 'pool_id');
    }

    /**
     * Get all winners for this pool
     */
    public function winners()
    {
        return $this->hasMany(SquaresPoolWinner::class, 'pool_id');
    }

    /**
     * Get total pot for this pool
     */
    public function getTotalPotAttribute()
    {
        $claimedSquares = $this->squares()->whereNotNull('player_id')->count();
        return $this->entry_fee * $claimedSquares;
    }

    /**
     * Get claimed squares count
     */
    public function getClaimedSquaresCountAttribute()
    {
        return $this->squares()->whereNotNull('player_id')->count();
    }

    /**
     * Get available squares count
     */
    public function getAvailableSquaresCountAttribute()
    {
        return 100 - $this->claimed_squares_count;
    }
}
