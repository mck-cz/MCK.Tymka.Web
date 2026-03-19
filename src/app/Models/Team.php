<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasUuids;

    protected $fillable = [
        'club_id',
        'season_id',
        'name',
        'sport',
        'age_category',
        'color',
        'is_active',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function recurrenceRules(): HasMany
    {
        return $this->hasMany(RecurrenceRule::class);
    }

    public function teamPosts(): HasMany
    {
        return $this->hasMany(TeamPost::class);
    }
}
