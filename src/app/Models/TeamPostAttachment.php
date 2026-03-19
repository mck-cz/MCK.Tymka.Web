<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamPostAttachment extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'uploaded_by',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function teamPost(): BelongsTo
    {
        return $this->belongsTo(TeamPost::class, 'post_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
