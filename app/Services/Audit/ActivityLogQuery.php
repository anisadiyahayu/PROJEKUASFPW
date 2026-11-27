<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Audit\ActivitySubject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Filter riwayat aktivitas dipakai bersama oleh halaman dan ekspor PDF,
 * supaya isi PDF selalu mengikuti filter yang sedang aktif (BR-30).
 */
final class ActivityLogQuery
{
    public const EVENTS = ['created', 'updated', 'deleted', 'restored'];

    /**
     * @return array{q: string, event: string, subject_type: string, user_id: int|null, dari: string|null, sampai: string|null}
     */
    public function filters(Request $request): array
    {
        $user = $request->user();
        $isPetugas = $user?->hasRole(User::ROLE_PETUGAS) ?? false;

        $q = $request->query('q');
        $userId = $request->query('user_id');

        return [
            'q' => is_string($q) ? trim($q) : '',
            'event' => $this->allowed($request->query('event'), self::EVENTS),
            'subject_type' => $this->allowed($request->query('subject_type'), ActivitySubject::all()),
            'user_id' => $isPetugas
                ? $user->id
                : (is_numeric($userId) ? (int) $userId : null),
            'dari' => $this->date($request->query('dari')),
            'sampai' => $this->date($request->query('sampai')),
        ];
    }

    /**
     * @param  array{q: string, event: string, subject_type: string, user_id: int|null, dari: string|null, sampai: string|null}  $filters
     */
    public function builder(array $filters, User $user): Builder
    {
        $isPetugas = $user->hasRole(User::ROLE_PETUGAS);

        return ActivityLog::query()
            ->with('user')
            ->when($isPetugas, fn (Builder $query) => $query->where('user_id', $user->id))
            ->when(! $isPetugas && $filters['user_id'] !== null, fn (Builder $query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['event'] !== '', fn (Builder $query) => $query->where('event', $filters['event']))
            ->when($filters['subject_type'] !== '', fn (Builder $query) => $query->where('subject_type', $filters['subject_type']))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $q = $filters['q'];
                $query->where(function (Builder $builder) use ($q) {
                    $builder->where('description', 'like', "%{$q}%")
                        ->orWhere('event', 'like', "%{$q}%");
                });
            })
            ->when($filters['dari'] !== null, fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['dari']))
            ->when($filters['sampai'] !== null, fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['sampai']))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @param  list<string>  $allowed
     */
    private function allowed(mixed $value, array $allowed): string
    {
        return is_string($value) && in_array($value, $allowed, true) ? $value : '';
    }

    private function date(mixed $value): ?string
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (Throwable) {
            return null;
        }

        return $date->format('Y-m-d') === $value ? $value : null;
    }
}
