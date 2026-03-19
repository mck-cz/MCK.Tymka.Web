<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomFieldDefinition extends Model
{
    use HasUuids;

    protected $fillable = [
        'club_id',
        'entity_type',
        'name',
        'display_name',
        'field_type',
        'options',
        'default_value',
        'placeholder',
        'help_text',
        'suffix',
        'is_required',
        'validation_min',
        'validation_max',
        'validation_regex',
        'visibility_read',
        'visibility_write',
        'show_in_registration',
        'show_in_roster',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'show_in_registration' => 'boolean',
            'show_in_roster' => 'boolean',
            'is_active' => 'boolean',
            'validation_min' => 'decimal:2',
            'validation_max' => 'decimal:2',
        ];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'definition_id');
    }
}
