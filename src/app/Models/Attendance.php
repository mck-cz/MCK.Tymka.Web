<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'team_membership_id',
        'rsvp_status',
        'rsvp_note',
        'responded_by',
        'responded_at',
        'actual_status',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'checked_at' => 'datetime',
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

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
