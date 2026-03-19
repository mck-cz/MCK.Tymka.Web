<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbsencePeriod extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'created_by',
        'reason_type',
        'reason_note',
        'starts_at',
        'ends_at',
        'apply_to_teams',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'apply_to_teams' => 'array',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function absencePeriodMembers(): HasMany
    {
        return $this->hasMany(AbsencePeriodMember::class);
    }
}
