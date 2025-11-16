<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameRewardType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'game_reward_types';

    protected $fillable = [
        'name',
        'description',
        'reward1_percent',
        'reward2_percent',
        'reward3_percent',
        'reward4_percent',
        'reward5_percent',
        'reward6_percent',
        'reward7_percent',
        'reward8_percent',
        'reward9_percent',
        'reward_other_percent',
        'reward_misc_percent',
    ];

    protected $casts = [
        'reward1_percent' => 'decimal:4',
        'reward2_percent' => 'decimal:4',
        'reward3_percent' => 'decimal:4',
        'reward4_percent' => 'decimal:4',
        'reward5_percent' => 'decimal:4',
        'reward6_percent' => 'decimal:4',
        'reward7_percent' => 'decimal:4',
        'reward8_percent' => 'decimal:4',
        'reward9_percent' => 'decimal:4',
        'reward_other_percent' => 'decimal:4',
        'reward_misc_percent' => 'decimal:4',
    ];

    /**
     * Relationship: Game Reward Type has many Squares Pools
     */
    public function squaresPools()
    {
        return $this->hasMany(SquaresPool::class, 'game_reward_type_id');
    }
}
