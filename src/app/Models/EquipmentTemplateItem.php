<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentTemplateItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'label',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function equipmentTemplate(): BelongsTo
    {
        return $this->belongsTo(EquipmentTemplate::class, 'template_id');
    }
}
