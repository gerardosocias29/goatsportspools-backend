<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class LeagueParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'league_id',
        'user_id'
    ];
}
