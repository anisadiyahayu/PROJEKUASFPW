<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Audit\ActivitySubject;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * PT-10, AD-14, dan DR-16: riwayat perubahan dan audit log.
 *
 * PRD 9 no. 1 & 2 dikonfirmasi sebagai satu halaman gabungan:
 * - Admin dan Direksi melihat seluruh aktivitas.
 * - Petugas hanya melihat aktivitas miliknya sendiri.
 */
class ActivityLogController extends Controller
{
    private const EVENTS = ['created', 'updated', 'deleted', 'restored'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $isPetugas = $user->hasRole(User::ROLE_PETUGAS);

        $q = $request->query('q');
        $userId = $request->query('user_id');

        $filters = [
            'q' => is_string($q) ? trim($q) : '',
            'event' => $this->allowed($request->query('event'), self::EVENTS),
            'subject_type' => $this->allowed($request->query('subject_type'), ActivitySubject::all()),
            'user_id' => $isPetugas
                ? $user->id
                : (is_numeric($userId) ? (int) $userId : null),
            'dari' => $this->date($request->query('dari')),
            'sampai' => $this->date($request->query('sampai')),
        ];

        $logs = ActivityLog::query()
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
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('audit.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'events' => self::EVENTS,
            'subjects' => ActivitySubject::options(),
            'users' => $isPetugas ? collect() : User::orderBy('name')->get(),
            'isPetugas' => $isPetugas,
        ]);
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
