<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class League extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'password',
        'league_id'
    ];

    protected $hidden = [
        'password'
    ];

    public function created_by() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
