<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentRequest extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'club_id',
        'team_id',
        'created_by',
        'name',
        'description',
        'amount',
        'currency',
        'payment_type',
        'due_date',
        'variable_symbol_prefix',
        'bank_account',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberPayments(): HasMany
    {
        return $this->hasMany(MemberPayment::class);
    }
}
