<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingAdjustment extends Model
{
    public const TYPE_DISKON = 'diskon';
    public const TYPE_MARKUP = 'markup';

    protected $fillable = [
        'billing_id', 'created_by', 'adjustment_type', 'adjustment_value',
        'adjustment_amount', 'disposition_note',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_value' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
        ];
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
