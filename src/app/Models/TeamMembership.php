<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMembership extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'status',
        'position',
        'jersey_number',
        'federation_id',
        'federation_status',
        'federation_registered_at',
        'federation_membership_valid_until',
        'federation_link_type',
        'federation_external_url',
        'license_type',
        'license_valid_until',
        'attendance_required',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'federation_registered_at' => 'date',
            'federation_membership_valid_until' => 'date',
            'license_valid_until' => 'date',
            'attendance_required' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(Nomination::class);
    }
}
