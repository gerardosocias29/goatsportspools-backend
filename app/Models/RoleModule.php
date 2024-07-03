<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class RoleModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
    ];

    public function sub_modules() {
        return $this->hasMany(RoleModule::class, 'parent_id', 'id');
    }
}
