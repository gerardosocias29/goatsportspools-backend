<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'pool_id', 'league_id', 'wager_type_id', 'odd_id', 
        'team_pick', 'picked_odd', 'wager_amount', 'wager_result'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pool()
    {
        return $this->belongsTo(Pool::class);
    }

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function wagerType()
    {
        return $this->belongsTo(WagerType::class);
    }

    public function odd()
    {
        return $this->belongsTo(Odd::class);
    }
}