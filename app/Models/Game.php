<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'game_datetime' => 'datetime',
    ];

    protected $fillable = [
        'game_datetime', 'time_zone', 'league_id', 'league',
        'home_team_id', 'visitor_team_id', 'location',
        'city', 'state', 'home_team_score', 'visitor_team_score',
        'external_game_id', 'game_nickname', 'game_date_description',
        'game_description', 'game_status',
        'home_q1_score', 'home_q2_score', 'home_q3_score', 'home_q4_score',
        'visitor_q1_score', 'visitor_q2_score', 'visitor_q3_score', 'visitor_q4_score',
        // Cumulative scores for Squares Pools
        'q1_home', 'q1_visitor',
        'half_home', 'half_visitor',
        'q3_home', 'q3_visitor',
        'final_home', 'final_visitor'
    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function odd()
    {
        return $this->belongsTo(Odd::class, 'id', 'game_id');
    }

    public function home_team()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function visitor_team()
    {
        return $this->belongsTo(Team::class, 'visitor_team_id');
    }

    public function timezone()
    {
        return $this->belongsTo(Timezone::class, 'time_zone');
    }
}