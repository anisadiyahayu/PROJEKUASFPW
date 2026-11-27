<?php

namespace App\Support\Audit;

use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\Invoice;
use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\TechnicalReport;

/**
 * Pemetaan tipe subjek polimorfik ACTIVITY_LOGS ke label antarmuka.
 */
final class ActivitySubject
{
    public const LABELS = [
        Owner::class => 'Pemilik',
        Ship::class => 'Kapal',
        Opnam::class => 'Opnam',
        OpnamItem::class => 'Item Opnam',
        Billing::class => 'Tagihan',
        BillingAdjustment::class => 'Penyesuaian Tagihan',
        Tariff::class => 'Tarif',
        Invoice::class => 'Invoice',
        TechnicalReport::class => 'Technical Report',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::LABELS);
    }

    public static function label(?string $type): string
    {
        if ($type === null) {
            return '-';
        }

        return self::LABELS[$type] ?? class_basename($type);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return self::LABELS;
    }
}
