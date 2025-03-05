<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'stream_url', 'event_date', 'status', 'active_item_id'];

    public function items()
    {
        return $this->hasMany(AuctionItem::class);
    }

    public function auctionBids() {
        return $this->hasMany(AuctionBid::class);
    }

    public function joinedUsers() {
        return $this->hasMany(AuctionUser::class);
    }
}
