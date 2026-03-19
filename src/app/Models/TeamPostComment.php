<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamPostComment extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'user_id',
        'body',
    ];

    public function teamPost(): BelongsTo
    {
        return $this->belongsTo(TeamPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
