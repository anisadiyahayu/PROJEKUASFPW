<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Opnam extends Model
{
    public const STATUS_DRAFT = 'draft';
<<<<<<< HEAD
    public const STATUS_MENUNGGU_REVIEW = 'menunggu_review';
    public const STATUS_PERLU_REVISI = 'perlu_revisi';
    public const STATUS_MENUNGGU_APPROVAL_DIREKSI = 'menunggu_approval_direksi';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DISETUJUI = 'disetujui';
=======

    public const STATUS_MENUNGGU_REVIEW = 'menunggu_review';

    public const STATUS_PERLU_REVISI = 'perlu_revisi';

    public const STATUS_MENUNGGU_APPROVAL_DIREKSI = 'menunggu_approval_direksi';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_DISETUJUI = 'disetujui';

>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
    public const STATUS_DOKUMEN_TERBIT = 'dokumen_terbit';

    protected $fillable = [
        'opnam_number', 'ship_id', 'created_by', 'status', 'submitted_at', 'forwarded_at',
<<<<<<< HEAD
        'tanggal_opnam', 'catatan', 'lokasi', 'adjustment_amount', 'total_biaya',
=======
        'tanggal_opnam', 'catatan', 'total_biaya', 'jenis_kapal',
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'forwarded_at' => 'datetime',
            'tanggal_opnam' => 'date',
<<<<<<< HEAD
            'adjustment_amount' => 'decimal:2',
=======
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
            'total_biaya' => 'decimal:2',
        ];
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OpnamItem::class)->orderBy('sort_order');
    }

    public function attachments(): HasManyThrough
    {
        return $this->hasManyThrough(OpnamAttachment::class, OpnamItem::class);
    }

    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class);
    }

    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable');
    }
}
