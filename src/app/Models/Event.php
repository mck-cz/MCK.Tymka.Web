<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasUuids;

    protected $fillable = [
        'club_id',
        'team_id',
        'venue_id',
        'location',
        'created_by',
        'event_type',
        'title',
        'starts_at',
        'ends_at',
        'recurrence_rule_id',
        'rsvp_deadline',
        'nomination_deadline',
        'min_capacity',
        'max_capacity',
        'instructions',
        'notes',
        'status',
        'cancel_reason',
        'rescheduled_to',
        'cancelled_by',
        'cancelled_at',
        'completed_at',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'rsvp_deadline' => 'datetime',
            'nomination_deadline' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Compute effective status based on time threshold from club settings.
     * scheduled → in_progress (when within threshold before start)
     * scheduled → past (when event has ended but not completed)
     */
    public function getEffectiveStatusAttribute(): string
    {
        if (in_array($this->status, ['cancelled', 'completed'])) {
            return $this->status;
        }

        $club = $this->relationLoaded('club') ? $this->club : Club::find($this->club_id);
        $minutesBefore = $club?->settings['event_in_progress_minutes'] ?? 60;

        $inProgressAt = $this->starts_at->copy()->subMinutes($minutesBefore);
        $endsAt = $this->ends_at ?? $this->starts_at->copy()->addHours(2);

        if (now()->gte($endsAt)) {
            return 'past';
        }

        if (now()->gte($inProgressAt)) {
            return 'in_progress';
        }

        return 'scheduled';
    }

    /**
     * Check if attendance can be recorded (event is in_progress, past, or completed).
     */
    public function getCanRecordAttendanceAttribute(): bool
    {
        return in_array($this->effective_status, ['in_progress', 'past', 'completed']);
    }

    /**
     * Check if attendance has been fully recorded.
     */
    public function getAttendanceRecordedAttribute(): bool
    {
        if ($this->relationLoaded('attendances')) {
            return $this->attendances->isNotEmpty()
                && $this->attendances->every(fn ($a) => $a->actual_status !== null);
        }

        return $this->attendances()->whereNotNull('actual_status')->exists()
            && !$this->attendances()->whereNull('actual_status')->exists();
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recurrenceRule(): BelongsTo
    {
        return $this->belongsTo(RecurrenceRule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(Nomination::class);
    }

    public function eventEquipment(): HasMany
    {
        return $this->hasMany(EventEquipment::class);
    }

    public function eventReminders(): HasMany
    {
        return $this->hasMany(EventReminder::class);
    }

    public function eventComments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function eventResult(): HasOne
    {
        return $this->hasOne(EventResult::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeForTeam(Builder $query, string $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }
}
