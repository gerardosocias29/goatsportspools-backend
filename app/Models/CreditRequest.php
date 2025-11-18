<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'requester_id',
        'commissioner_id',
        'amount',
        'reason',
        'status',
        'approved_at',
        'approved_by',
        'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function pool()
    {
        return $this->belongsTo(SquaresPool::class, 'pool_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function commissioner()
    {
        return $this->belongsTo(User::class, 'commissioner_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForCommissioner($query, $commissionerId)
    {
        return $query->where('commissioner_id', $commissionerId);
    }

    public function scopeForPool($query, $poolId)
    {
        return $query->where('pool_id', $poolId);
    }
}
