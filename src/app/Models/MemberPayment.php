<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MemberPayment extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'payment_request_id',
        'user_id',
        'child_id',
        'variable_symbol',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
        'confirmed_by',
        'thanked_at',
        'qr_payload',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'thanked_at' => 'datetime',
        ];
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->amount - (float) $this->paid_amount;
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function paymentReceipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class);
    }
}
