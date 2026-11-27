<?php

use App\Models\ActivityLog;
use App\Models\Owner;
use App\Models\User;
use App\Services\Audit\ActivityLogPdfExporter;
use App\Services\Audit\ActivityLogQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

function hazelPdfBuatUser(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role).' PDF',
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function hazelPdfBuatLog(User $user, string $event, string $description): ActivityLog
{
    return ActivityLog::create([
        'user_id' => $user->id,
        'event' => $event,
        'subject_type' => Owner::class,
        'subject_id' => 1,
        'description' => $description,
    ]);
}

test('tamu tidak dapat mengekspor riwayat aktivitas', function () {
    $this->get(route('activity-logs.pdf'))
        ->assertRedirect(route('login'));
});

test('semua role dapat mengunduh PDF riwayat aktivitas', function () {
    foreach (['admin', 'direksi', 'petugas'] as $role) {
        $user = hazelPdfBuatUser($role, 'pdf-'.$role);
        hazelPdfBuatLog($user, 'created', 'Aktivitas '.$role);

        $response = $this->actingAs($user)->get(route('activity-logs.pdf'));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('application/pdf')
            ->and($response->headers->get('Content-Disposition'))->toContain('attachment; filename="riwayat-aktivitas-')
            ->and(substr((string) $response->getContent(), 0, 4))->toBe('%PDF');
    }
});

test('ekspor PDF memakai filter dan cakupan role yang sama dengan halaman', function () {
    $admin = hazelPdfBuatUser('admin', 'pdf-filter-admin');
    $petugas = hazelPdfBuatUser('petugas', 'pdf-filter-petugas');
    $logAdmin = hazelPdfBuatLog($admin, 'created', 'Pemilik dibuat admin');
    $logPetugas = hazelPdfBuatLog($petugas, 'updated', 'Pemilik diperbarui petugas');

    $request = Request::create('/riwayat-aktivitas?event=updated', 'GET');
    $request->setUserResolver(fn () => $petugas);
    $filters = app(ActivityLogQuery::class)->filters($request);
    $logs = app(ActivityLogQuery::class)->builder($filters, $petugas)->get();

    expect($logs->pluck('id')->all())->toBe([$logPetugas->id]);

    $html = app(ActivityLogPdfExporter::class)->html($logs, $filters, $petugas);
    expect($html)->toContain('Pemilik diperbarui petugas')
        ->not->toContain('Pemilik dibuat admin');

    $requestAdmin = Request::create('/riwayat-aktivitas?user_id='.$admin->id.'&q=dibuat', 'GET');
    $requestAdmin->setUserResolver(fn () => $admin);
    $filtersAdmin = app(ActivityLogQuery::class)->filters($requestAdmin);
    $logsAdmin = app(ActivityLogQuery::class)->builder($filtersAdmin, $admin)->get();

    expect($logsAdmin->pluck('id')->all())->toBe([$logAdmin->id]);

    $htmlAdmin = app(ActivityLogPdfExporter::class)->html($logsAdmin, $filtersAdmin, $admin);
    expect($htmlAdmin)->toContain('Pemilik dibuat admin')
        ->not->toContain('Pemilik diperbarui petugas');
});

test('nama berkas PDF mengikuti filter tanggal aktif', function () {
    $admin = hazelPdfBuatUser('admin', 'pdf-nama-berkas');
    hazelPdfBuatLog($admin, 'created', 'Aktivitas berfilter');

    $response = $this->actingAs($admin)->get(route('activity-logs.pdf', [
        'dari' => '2026-11-01',
        'sampai' => '2026-11-30',
        'event' => 'created',
    ]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))
        ->toContain('riwayat-aktivitas-2026-11-01-2026-11-30.pdf');
});

test('halaman riwayat aktivitas menampilkan tautan ekspor PDF', function () {
    $admin = hazelPdfBuatUser('admin', 'pdf-tautan');
    hazelPdfBuatLog($admin, 'created', 'Aktivitas dengan tautan');

    $this->actingAs($admin)
        ->get(route('activity-logs.index'))
        ->assertOk()
        ->assertSee(route('activity-logs.pdf'))
        ->assertSee('Ekspor PDF');
});
