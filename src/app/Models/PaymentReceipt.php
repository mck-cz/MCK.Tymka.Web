<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'member_payment_id',
        'file_path',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function memberPayment(): BelongsTo
    {
        return $this->belongsTo(MemberPayment::class);
    }
}
