<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCommentWatcher extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'watching_since',
    ];

    protected function casts(): array
    {
        return [
            'watching_since' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
