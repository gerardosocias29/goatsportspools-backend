<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayoffBracket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'participant_id',
        'bracket_name',
        'bracket_index',
        'status',
        'finalized_at',
        'total_points',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(PlayoffPoolParticipant::class, 'participant_id');
    }

    public function picks()
    {
        return $this->hasMany(PlayoffBracketPick::class, 'bracket_id');
    }
}
