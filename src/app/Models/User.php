<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'nickname',
        'email',
        'phone',
        'password',
        'avatar_path',
        'address',
        'birth_date',
        'sex',
        'is_minor',
        'can_self_manage',
        'status',
        'claimed_by',
        'claimed_at',
        'created_by_role',
        'locale',
        'notification_preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_minor' => 'boolean',
            'can_self_manage' => 'boolean',
            'notification_preferences' => 'array',
            'claimed_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1) . mb_substr($this->last_name, 0, 1));
    }

    public function guardianOf(): HasMany
    {
        return $this->hasMany(UserGuardian::class, 'guardian_id');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(UserGuardian::class, 'child_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_guardians', 'guardian_id', 'child_id');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function clubMemberships(): HasMany
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_memberships')->withPivot('role', 'status', 'joined_at');
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_memberships')->withPivot('role', 'status', 'position', 'joined_at');
    }

    /**
     * Get IDs of children who are active members of a given club.
     */
    public function getChildrenIdsInClub(string $clubId): \Illuminate\Support\Collection
    {
        return $this->children()
            ->whereHas('clubMemberships', fn ($q) => $q->where('club_id', $clubId)->where('status', 'active'))
            ->pluck('users.id');
    }

    public function calendarFeeds(): HasMany
    {
        return $this->hasMany(CalendarFeed::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
