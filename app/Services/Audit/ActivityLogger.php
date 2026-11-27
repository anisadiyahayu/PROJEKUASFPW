<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Satu-satunya pintu penulisan ACTIVITY_LOGS.
 *
 * Nilai lama/baru disimpan sebagai json apa adanya (tanpa perhitungan ulang),
 * sesuai kolom old_values dan new_values pada ERD.
 */
class ActivityLogger
{
    public function log(
        string $event,
        Model $subject,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $this->ipAddress(),
        ]);
    }

    private function ipAddress(): ?string
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        return request()->ip();
    }
}
