<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberClaimRequest extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'placeholder_id',
        'club_id',
        'team_id',
        'requested_by',
        'target_email',
        'target_phone',
        'token',
        'link_type',
        'matched_user_id',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function placeholder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placeholder_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }
}
