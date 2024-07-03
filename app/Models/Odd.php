<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'game_id', 'favored_team_id', 'underdog_team_id', 
        'favored_points', 'underdog_points', 'favored_ml', 
        'underdog_ml', 'over_total', 'under_total', 'created_by'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function favored_team()
    {
        return $this->belongsTo(Team::class, 'favored_team_id');
    }

    public function underdog_team()
    {
        return $this->belongsTo(Team::class, 'underdog_team_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}