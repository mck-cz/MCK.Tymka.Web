<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nomination extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'team_membership_id',
        'source_team_id',
        'status',
        'priority',
        'nominated_by',
        'responded_by',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function teamMembership(): BelongsTo
    {
        return $this->belongsTo(TeamMembership::class);
    }

    public function sourceTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'source_team_id');
    }

    public function nominatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nominated_by');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
