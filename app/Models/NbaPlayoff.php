<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NbaPlayoff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nba_playoffs';

    protected $fillable = [
        'year',
        'name',
        'status',
        'current_round',
    ];

    public function teams()
    {
        return $this->hasMany(NbaPlayoffTeam::class, 'playoff_id');
    }

    public function pools()
    {
        return $this->hasMany(PlayoffPool::class, 'playoff_id');
    }
}
