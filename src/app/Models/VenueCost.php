<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VenueCost extends Model
{
    use HasUuids;

    protected $fillable = [
        'club_id',
        'team_id',
        'name',
        'cost_per_event',
        'currency',
        'split_method',
        'billing_period',
        'include_event_types',
        'bank_account',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_event' => 'decimal:2',
            'include_event_types' => 'array',
            'is_active' => 'boolean',
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

    public function venueCostSettlements(): HasMany
    {
        return $this->hasMany(VenueCostSettlement::class);
    }
}
