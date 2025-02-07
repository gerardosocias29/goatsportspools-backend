<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $username;
    public $amount;

    public function __construct($username, $amount) {
        $this->username = $username;
        $this->amount = $amount;
    }

    public function broadcastOn() {
        return ['bidding-channel'];
    }

    public function broadcastAs() {
        return 'new-bid';
    }
}
