<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentTemplate extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'club_id',
        'event_type',
        'name',
        'sort_order',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function equipmentTemplateItems(): HasMany
    {
        return $this->hasMany(EquipmentTemplateItem::class, 'template_id');
    }
}
