<?php

namespace App\CustomLibraries;

use Pusher\Pusher;

class PushNotification
{
  protected static ?Pusher $pusher = null;

  protected static function initialize(): void
  {
    if (!self::$pusher) {
      $options = [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS'  => true
      ];

      $appKey = env('PUSHER_APP_KEY');
      $appSecret = env('PUSHER_APP_SECRET');
      $appId = env('PUSHER_APP_ID');

      if (!$appKey || !$appSecret || !$appId) {
        throw new \Exception("Pusher credentials are missing");
      }

      self::$pusher = new Pusher($appKey, $appSecret, $appId, $options);
    }
  }

  public static function pushNotify(array $data, string|int $uid): void
  {
    self::trigger("notification-channel-{$uid}", "notification-event-{$uid}", $data);
  }

  public static function notifyActiveAuction($data, $id = "all"): void
  {
    self::trigger("bidding-channel", "active-auction-event-{$id}", $data);
  }

  public static function notifyActiveItem($data): void
  {
    self::trigger("bidding-channel", "active-item-event", $data);
  }

  public static function notifyAuctionJoined($data): void
  {
    self::trigger("bidding-channel", "auction-members", $data);
  }

  public static function notifyBid($data): void
  {
    self::trigger("bidding-channel", "bid-event", $data);
  }

  protected static function trigger(string $channel, string $event, $data): void
  {
    self::initialize();
    self::$pusher->trigger($channel, $event, $data);
  }
}
