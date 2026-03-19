<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurrenceRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'club_id',
        'team_id',
        'name',
        'event_type',
        'frequency',
        'interval',
        'day_of_week',
        'week_parity',
        'nth_weekday',
        'time_start',
        'time_end',
        'venue_id',
        'instructions_template_id',
        'equipment_template_id',
        'auto_create_days_ahead',
        'auto_rsvp',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'auto_rsvp' => 'boolean',
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
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

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function instructionTemplate(): BelongsTo
    {
        return $this->belongsTo(InstructionTemplate::class);
    }

    public function equipmentTemplate(): BelongsTo
    {
        return $this->belongsTo(EquipmentTemplate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recurrenceExclusions(): HasMany
    {
        return $this->hasMany(RecurrenceExclusion::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
