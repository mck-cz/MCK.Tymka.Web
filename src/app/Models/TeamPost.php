<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamPost extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'team_id',
        'user_id',
        'title',
        'body',
        'post_type',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teamPostComments(): HasMany
    {
        return $this->hasMany(TeamPostComment::class, 'post_id');
    }

    public function pollOptions(): HasMany
    {
        return $this->hasMany(PollOption::class, 'post_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TeamPostAttachment::class, 'post_id');
    }
}
