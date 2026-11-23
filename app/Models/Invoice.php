<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'billing_id', 'issued_by', 'invoice_number', 'issue_date',
        'ship_name', 'hull_number', 'owner_name', 'owner_address',
        'subtotal_amount', 'adjustment_amount', 'total_amount', 'revision_count',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'revision_count' => 'integer',
        ];
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
