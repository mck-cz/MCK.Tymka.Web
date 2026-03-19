<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurrenceExclusion extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'recurrence_rule_id',
        'excluded_date',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'excluded_date' => 'date',
        ];
    }

    public function recurrenceRule(): BelongsTo
    {
        return $this->belongsTo(RecurrenceRule::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
