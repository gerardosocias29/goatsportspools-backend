<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NbaPlayoffTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'playoff_id',
        'team_id',
        'conference',
        'seed',
        'r1_beat_seed',
        'r1_games',
        'r2_beat_seed',
        'r2_games',
        'r3_beat_seed',
        'r3_games',
        'finals_beat_seed',
        'finals_games',
    ];

    public function playoff()
    {
        return $this->belongsTo(NbaPlayoff::class, 'playoff_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
