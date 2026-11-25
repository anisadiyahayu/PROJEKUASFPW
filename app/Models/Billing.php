<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Billing extends Model
{
    public const STATUS_MENUNGGU_APPROVAL = 'menunggu_approval';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'opnam_id', 'billing_number', 'status', 'subtotal_amount', 'adjustment_amount',
        'total_amount', 'decided_by', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function opnam(): BelongsTo
    {
        return $this->belongsTo(Opnam::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(BillingAdjustment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function technicalReport(): HasOne
    {
        return $this->hasOne(TechnicalReport::class);
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable');
    }
}
