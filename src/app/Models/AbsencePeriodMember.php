<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsencePeriodMember extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'absence_period_id',
        'user_id',
        'team_membership_id',
    ];

    public function absencePeriod(): BelongsTo
    {
        return $this->belongsTo(AbsencePeriod::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teamMembership(): BelongsTo
    {
        return $this->belongsTo(TeamMembership::class);
    }
}
