<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OpnamController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WorkCategoryController;
use App\Http\Controllers\WorkTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->dashboardRoute());
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

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
        Route::get('/opnams', [OpnamController::class, 'index'])->name('opnams.index');
        Route::resource('work-categories', WorkCategoryController::class)
            ->except('show')
            ->parameters(['work-categories' => 'workCategory']);
        Route::resource('work-types', WorkTypeController::class)
            ->except('show')
            ->parameters(['work-types' => 'workType']);
        Route::resource('units', UnitController::class)
            ->except('show');
    });

Route::middleware(['auth', 'role:direksi'])
    ->prefix('direksi')
    ->name('direksi.')
    ->group(function () {
        Route::view('/dashboard', 'dashboard.direksi')->name('dashboard');
    });
