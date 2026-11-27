<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    public const UPDATED_AT = null; // tabel hanya punya created_at

    protected $fillable = [
        'statusable_type', 'statusable_id', 'from_status', 'to_status', 'note', 'changed_by',
    ];

    public function statusable(): MorphTo
    {
        return $this->morphTo();
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
