<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penalty extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'penalty_rule_id',
        'event_id',
        'user_id',
        'trigger_type',
        'original_rsvp',
        'amount',
        'count_as_attendance',
        'waived',
        'waived_by',
        'waived_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'count_as_attendance' => 'boolean',
            'waived' => 'boolean',
        ];
    }

    public function penaltyRule(): BelongsTo
    {
        return $this->belongsTo(PenaltyRule::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }
}
