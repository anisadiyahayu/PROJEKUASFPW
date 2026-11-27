<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpnamAttachment extends Model
{
    protected $fillable = [
        'opnam_item_id', 'uploaded_by', 'file_path', 'original_name', 'mime_type', 'file_size',
        'keterangan',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    public function opnamItem(): BelongsTo
    {
        return $this->belongsTo(OpnamItem::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
