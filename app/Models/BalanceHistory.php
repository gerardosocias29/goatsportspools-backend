<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class BalanceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'league_id',
        'user_id',
        'amount',
        'type'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
