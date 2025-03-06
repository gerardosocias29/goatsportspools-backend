<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'first_name',
        'last_name',
        'address',
        'city',
        'state',
        'zipcode',
        'username',
        'role_id',
        'clerk_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // 'role_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role() {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function leagues() {
        return $this->belongsToMany(League::class, 'league_participants');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function win_bets() {
        return $this->hasMany(Bet::class)->where('wager_result', 'win')->whereNull('bet_group_id');
    }

    public function lose_bets() {
        return $this->hasMany(Bet::class)->where('wager_result', 'lose')->whereNull('bet_group_id');
    }

    public function tie_bets() {
        return $this->hasMany(Bet::class)->where('wager_result', 'push')->whereNull('bet_group_id');
    }

    public function auction() {
        return $this->belongsTo(AuctionUser::class, 'user_id', 'id');
    }

    public function auctions() {
        return $this->hasMany(AuctionUser::class, 'user_id', 'id');
    }

    public function auctionItems() {
        return $this->hasMany(AuctionItem::class, 'sold_to', 'id');
    }
}
