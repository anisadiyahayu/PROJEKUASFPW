<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalReport extends Model
{
    protected $fillable = [
        'billing_id', 'issued_by', 'report_number', 'issue_date',
        'ship_name', 'hull_number', 'owner_name', 'owner_address',
    ];

    protected function casts(): array
    {
        return ['issue_date' => 'date'];
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
