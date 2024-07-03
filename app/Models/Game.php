<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'game_datetime', 'time_zone', 'league_id', 
        'home_team_id', 'visitor_team_id', 'location', 
        'city', 'state', 'home_team_score', 'visitor_team_score'
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