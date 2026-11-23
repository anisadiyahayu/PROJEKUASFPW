<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpnamItem extends Model
{
    protected $fillable = [
        'opnam_id', 'tariff_id', 'volume', 'unit_price', 'subtotal', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function opnam(): BelongsTo
    {
        return $this->belongsTo(Opnam::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OpnamAttachment::class);
    }
}
