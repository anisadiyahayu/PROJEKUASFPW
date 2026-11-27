<?php

namespace App\Support;

use App\Models\Opnam;

/**
 * Label tampilan untuk nilai OPNAMS.status.
 *
 * Nilai mentah tetap mengikuti ERD (lihat App\Models\Opnam::STATUS_*),
 * kelas ini hanya memetakan nilai tersebut ke teks antarmuka.
 */
final class OpnamStatus
{
    public const LABELS = [
        Opnam::STATUS_DRAFT => 'Draft',
        Opnam::STATUS_MENUNGGU_REVIEW => 'Menunggu Review',
        Opnam::STATUS_PERLU_REVISI => 'Perlu Revisi',
        Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI => 'Menunggu Approval Direksi',
        Opnam::STATUS_DITOLAK => 'Ditolak',
        Opnam::STATUS_DISETUJUI => 'Disetujui',
        Opnam::STATUS_DOKUMEN_TERBIT => 'Dokumen Terbit',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::LABELS);
    }

    public static function label(?string $status): string
    {
        return self::LABELS[$status] ?? (string) $status;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return self::LABELS;
    }
}
