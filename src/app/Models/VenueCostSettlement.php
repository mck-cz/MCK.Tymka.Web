<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VenueCostSettlement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'venue_cost_id',
        'period_from',
        'period_to',
        'total_events',
        'total_cost',
        'total_attendances',
        'cost_per_attendance',
        'status',
        'generated_at',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'total_cost' => 'decimal:2',
            'cost_per_attendance' => 'decimal:2',
            'generated_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function venueCost(): BelongsTo
    {
        return $this->belongsTo(VenueCost::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function venueCostMemberShares(): HasMany
    {
        return $this->hasMany(VenueCostMemberShare::class, 'settlement_id');
    }
}
