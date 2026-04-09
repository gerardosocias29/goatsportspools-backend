<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayoffPoolParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pool_id',
        'user_id',
        'credits_available',
        'brackets_count',
        'total_points',
    ];

    public function pool()
    {
        return $this->belongsTo(PlayoffPool::class, 'pool_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brackets()
    {
        return $this->hasMany(PlayoffBracket::class, 'participant_id');
    }
}
