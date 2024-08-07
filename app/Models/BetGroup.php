<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BetGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'wager_type_id',
        'wager_amount',
        'wager_win_amount',
        'adjustment',
        'wager_result',
    ];

    public function bets() {
        return $this->hasMany(Bet::class);
    }

    public function wagerType()
    {
        return $this->belongsTo(WagerType::class, 'wager_type_id');
    }
}