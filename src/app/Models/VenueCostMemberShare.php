<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueCostMemberShare extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'settlement_id',
        'user_id',
        'attendance_count',
        'amount_due',
        'variable_symbol',
        'qr_payload',
        'status',
        'paid_at',
        'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function venueCostSettlement(): BelongsTo
    {
        return $this->belongsTo(VenueCostSettlement::class, 'settlement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
