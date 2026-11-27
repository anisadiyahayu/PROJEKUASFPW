<?php

<<<<<<< HEAD
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\ShipController;
use App\Http\Controllers\Audit\ActivityLogController;
use App\Http\Controllers\Direksi\ApprovedTransactionController;
use App\Http\Controllers\OpnamController;
use App\Http\Controllers\Reports\ShipRepairHistoryController;
use App\Http\Controllers\Reports\WorkRecapitulationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
=======
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MaterialSpecController;
use App\Http\Controllers\OpnamController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WorkCategoryController;
use App\Http\Controllers\WorkTypeController;
use Illuminate\Http\Request;
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->dashboardRoute());
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');

<<<<<<< HEAD
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
=======
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.petugas')->name('dashboard');
        Route::get('/opnams', [OpnamController::class, 'indexMine'])->name('opnams.index');
        Route::get('/opnams/create', [OpnamController::class, 'create'])->name('opnams.create');
        Route::post('/opnams/create', [OpnamController::class, 'storeStep'])->name('opnams.store-step');
<<<<<<< HEAD
    });

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
=======
        Route::get('/opnams/{opnam}/edit', [OpnamController::class, 'edit'])->name('opnams.edit');
        Route::post('/opnams/{opnam}/items', [OpnamController::class, 'storeItem'])->name('opnams.items.store');
        Route::put('/opnams/{opnam}/items/{item}', [OpnamController::class, 'updateItem'])->name('opnams.items.update');
        Route::delete('/opnams/{opnam}/items/{item}', [OpnamController::class, 'destroyItem'])->name('opnams.items.destroy');
    });

>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.admin')->name('dashboard');
        Route::get('/opnams', [OpnamController::class, 'index'])->name('opnams.index');
<<<<<<< HEAD

        // AD-02: data pemilik dan kapal.
        Route::resource('owners', OwnerController::class)->except(['show']);
        Route::resource('ships', ShipController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| Direksi
|--------------------------------------------------------------------------
*/
=======
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
    });

>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
Route::middleware(['auth', 'role:direksi'])
    ->prefix('direksi')
    ->name('direksi.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.direksi')->name('dashboard');
<<<<<<< HEAD
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
=======
    });
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
