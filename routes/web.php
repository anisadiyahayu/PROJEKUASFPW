<?php

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ReviewOpnamController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Direksi\DokumenController;
use App\Http\Controllers\Direksi\TagihanController;
use App\Http\Controllers\MaterialSpecController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\ShipController;
use App\Http\Controllers\Audit\ActivityLogController;
use App\Http\Controllers\Direksi\ApprovedTransactionController;
use App\Http\Controllers\OpnamController;
use App\Http\Controllers\Reports\ShipRepairHistoryController;
use App\Http\Controllers\Reports\WorkRecapitulationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WorkCategoryController;
use App\Http\Controllers\WorkTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->dashboardRoute());
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');

    Route::post('/login', function (Request $request) {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $credentials = [
            'username' => strtolower(trim($data['username'])),
            'password' => $data['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Username atau password salah, atau akun tidak aktif.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->route($request->user()->dashboardRoute());
    })->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Petugas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.petugas')->name('dashboard');
        Route::get('/opnams', [OpnamController::class, 'indexMine'])->name('opnams.index');
        Route::get('/opnams/create', [OpnamController::class, 'create'])->name('opnams.create');
        Route::post('/opnams/create', [OpnamController::class, 'storeStep'])->name('opnams.store-step');
        Route::get('/opnams/{opnam}/edit', [OpnamController::class, 'edit'])->name('opnams.edit');
        Route::post('/opnams/{opnam}/items', [OpnamController::class, 'storeItem'])->name('opnams.items.store');
        Route::put('/opnams/{opnam}/items/{item}', [OpnamController::class, 'updateItem'])->name('opnams.items.update');
        Route::delete('/opnams/{opnam}/items/{item}', [OpnamController::class, 'destroyItem'])->name('opnams.items.destroy');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.admin')->name('dashboard');
        Route::get('/review-opnams', [ReviewOpnamController::class, 'index'])->name('review-opnams.index');
        Route::get('/review-opnams/{opnam}', [ReviewOpnamController::class, 'show'])->name('review-opnams.show');
        Route::post('/review-opnams/{opnam}/approve', [ReviewOpnamController::class, 'approve'])->name('review-opnams.approve');
        Route::post('/review-opnams/{opnam}/return-for-revision', [ReviewOpnamController::class, 'returnForRevision'])->name('review-opnams.return-for-revision');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/opnams', [OpnamController::class, 'index'])->name('opnams.index');

        // AD-02: data pemilik dan kapal.
        Route::resource('owners', OwnerController::class)->except(['show']);
        Route::resource('ships', ShipController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| Direksi
|--------------------------------------------------------------------------
*/
       

Route::middleware(['auth', 'role:direksi'])
    ->prefix('direksi')
    ->name('direksi.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.direksi')->name('dashboard');
        Route::get('/tagihans', [TagihanController::class, 'index'])->name('tagihans.index');
        Route::get('/tagihans/{billing}', [TagihanController::class, 'show'])->name('tagihans.show');
        Route::post('/tagihans/{billing}/adjustment', [TagihanController::class, 'applyAdjustment'])->name('tagihans.apply-adjustment');
        Route::post('/tagihans/{billing}/approve', [TagihanController::class, 'approve'])->name('tagihans.approve');
        Route::post('/tagihans/{billing}/reject', [TagihanController::class, 'reject'])->name('tagihans.reject');
        Route::get('/dokumen', [DokumenController::class, 'index'])->name('dokumen.index');
        Route::get('/dokumen/invoices/{invoice}/edit', [DokumenController::class, 'editInvoice'])->name('dokumen.edit-invoice');
        Route::put('/dokumen/invoices/{invoice}', [DokumenController::class, 'updateInvoice'])->name('dokumen.update-invoice');
        Route::get('/dokumen/invoices/{invoice}/download', [DokumenController::class, 'downloadInvoice'])->name('dokumen.download-invoice');
        Route::post('/dokumen/unduh', [DokumenController::class, 'unduh'])->name('dokumen.unduh');
        Route::get('/opnams/{opnam}', [OpnamController::class, 'show'])->name('opnams.show');
        Route::resource('work-categories', WorkCategoryController::class)
            ->except('show')
            ->parameters(['work-categories' => 'workCategory']);
        Route::resource('work-types', WorkTypeController::class)
            ->except('show')
            ->parameters(['work-types' => 'workType']);
        Route::resource('units', UnitController::class)
            ->except('show');
        Route::resource('material-specs', MaterialSpecController::class)
            ->except('show')
            ->parameters(['material-specs' => 'materialSpec']);

        Route::get('/transaksi-disetujui', [ApprovedTransactionController::class, 'index'])->name('approved.index');
    });

/*
|--------------------------------------------------------------------------
| Laporan dan riwayat perbaikan (Admin + Direksi)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,direksi'])->group(function () {
    // AD-11 dan DR-15: riwayat perbaikan kapal (satu halaman bersama).
    Route::get('/riwayat-perbaikan', [ShipRepairHistoryController::class, 'index'])->name('repair-history.index');
    Route::get('/riwayat-perbaikan/{ship}', [ShipRepairHistoryController::class, 'show'])
        ->withTrashed()
        ->name('repair-history.show');

    // AD-16 dan DR-14: laporan rekapitulasi pekerjaan.
    Route::get('/laporan/rekapitulasi', [WorkRecapitulationController::class, 'index'])
        ->name('reports.recapitulation.index');
    Route::get('/laporan/rekapitulasi/pdf', [WorkRecapitulationController::class, 'exportPdf'])
        ->name('reports.recapitulation.pdf');
    Route::get('/laporan/rekapitulasi/excel', [WorkRecapitulationController::class, 'exportExcel'])
        ->name('reports.recapitulation.excel');
    });

/*
|--------------------------------------------------------------------------
| Riwayat aktivitas dan audit log (semua role, cakupan diatur controller)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/riwayat-aktivitas', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});