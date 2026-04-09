<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayoffBracketPick extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bracket_id',
        'pick_type',
        'round',
        'conference',
        'picked_team_id',
        'picked_games',
        'base_points',
        'games_bonus',
        'seed_bonus',
        'scored_at',
    ];

    protected $casts = [
        'scored_at' => 'datetime',
    ];

    public function bracket()
    {
        return $this->belongsTo(PlayoffBracket::class, 'bracket_id');
    }

    public function pickedTeam()
    {
        return $this->belongsTo(Team::class, 'picked_team_id');
    }
}
