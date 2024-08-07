<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'allowed_modules'
    ];

    protected $casts = [
        'allowed_modules' => "json"
    ];

    public function allowed_modules() {
        return $this->hasMany(RoleModule::class, 'allowed_modules'); // i want to where allowed_modules in RoleModule.id
    }

    public function sub_modules() {
        return $this->hasMany(RoleModule::class, 'parent_id', 'id');
    }
}
