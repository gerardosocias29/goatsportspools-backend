<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nickname',
        'code',
        'conference',
        'image_url'
    ];

    public function homeGames()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function visitorGames()
    {
        return $this->hasMany(Game::class, 'visitor_team_id');
    }
}
