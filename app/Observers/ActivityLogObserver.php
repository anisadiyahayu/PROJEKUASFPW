<?php

namespace App\Observers;

use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\Invoice;
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\TechnicalReport;
use App\Services\Audit\ActivityLogger;
use App\Support\Audit\ActivitySubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Mencatat perubahan model ke ACTIVITY_LOGS tanpa mengubah model pemiliknya.
 *
 * Observer didaftarkan terpusat di AppServiceProvider agar modul lain
 * (opnam, tagihan, invoice) tetap menjadi milik pemilik aslinya.
 */
class ActivityLogObserver
{
    private const VERBS = [
        'created' => 'dibuat',
        'updated' => 'diperbarui',
        'deleted' => 'dihapus',
        'restored' => 'dipulihkan',
    ];

    public function __construct(private readonly ActivityLogger $logger) {}

    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $this->snapshot($model));
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except(['updated_at'])
            ->all();

        if ($changes === []) {
            return;
        }

        $original = [];
        foreach (array_keys($changes) as $attribute) {
            $original[$attribute] = $model->getOriginal($attribute);
        }

        $this->record($model, 'updated', $original, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->snapshot($model), null);
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored', null, $this->snapshot($model));
    }

    private function record(Model $model, string $event, ?array $oldValues, ?array $newValues): void
    {
        $this->logger->log($event, $model, $this->description($model, $event), $oldValues, $newValues);
    }

    private function description(Model $model, string $event): string
    {
        $label = ActivitySubject::label($model->getMorphClass());
        $identifier = $this->identifier($model);
        $subject = $identifier === null ? "{$label} #{$model->getKey()}" : "{$label} {$identifier}";

        return Str::limit("{$subject} ".self::VERBS[$event], 250, '…');
    }

    private function identifier(Model $model): ?string
    {
        $value = match (true) {
            $model instanceof Ship => trim($model->name.' '.$model->hull_number),
            $model instanceof Owner => $model->name,
            $model instanceof Opnam => $model->opnam_number,
            $model instanceof Billing => $model->billing_number,
            $model instanceof Invoice => $model->invoice_number,
            $model instanceof TechnicalReport => $model->report_number,
            $model instanceof BillingAdjustment => $model->adjustment_type,
            default => null,
        };

        return filled($value) ? (string) $value : null;
    }

    private function snapshot(Model $model): array
    {
        return collect($model->getAttributes())
            ->except(['updated_at'])
            ->all();
    }
}
