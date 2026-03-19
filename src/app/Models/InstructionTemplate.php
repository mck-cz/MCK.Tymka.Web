<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructionTemplate extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'club_id',
        'event_type',
        'name',
        'body',
        'sort_order',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
