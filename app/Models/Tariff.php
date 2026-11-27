<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Tariff extends Model
{
    public const UPDATED_AT = null; // tabel hanya punya created_at

    protected $fillable = [
        'material_spec_id', 'unit_id', 'created_by', 'price', 'effective_from',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'effective_from' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Tarif yang sudah tersimpan tidak dapat diubah. Buat tarif baru.');
        });
    }

    public function materialSpec(): BelongsTo
    {
        return $this->belongsTo(MaterialSpec::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opnamItems(): HasMany
    {
        return $this->hasMany(OpnamItem::class);
    }
}
