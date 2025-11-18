<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCreditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
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
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
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

    public function scopeForSuperadmin($query)
    {
        return $query->whereHas('requester', function ($q) {
            $q->where('role_id', 2); // Square Admins only
        });
    }
}
