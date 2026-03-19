<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'option_id',
        'user_id',
    ];

    public function pollOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
