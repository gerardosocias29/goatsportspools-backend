<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class NcaaTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['region', 'seed', 'school', 'nickname'];
}
