<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class AuctionUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['auction_id', 'user_id', 'status'];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
