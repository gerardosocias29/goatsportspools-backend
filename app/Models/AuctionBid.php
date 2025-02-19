<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class AuctionBid extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['auction_id', 'ncaa_team_id', 'user_id', 'bid_amount'];

    
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function ncaaTeam()
    {
        return $this->belongsTo(NcaaTeam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
