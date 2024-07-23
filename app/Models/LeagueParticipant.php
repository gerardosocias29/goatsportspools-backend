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

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rebuys() {
        return $this->hasMany(BalanceHistory::class, 'league_id', 'league_id')
                    ->where('user_id', $this->user_id);
    }
}