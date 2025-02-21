<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['auction_id', 'ncaa_team_id', 'name', 'description', 'sold_to', 'starting_bid', 'minimum_bid', 'target_bid', 'sold_amount', 'status'];

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionItemBid::class);
    }

    public function ncaa_team() {
        return $this->belongsTo(NcaaTeam::class);
    }
}
