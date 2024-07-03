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
        'bet_type',
        'wager_amount',
        'adjustment',
        'wager_result',
    ];
}