<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionItemBid extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'auction_item_id', 'bid_amount'];

    public function item()
    {
        return $this->belongsTo(AuctionItem::class, 'auction_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
