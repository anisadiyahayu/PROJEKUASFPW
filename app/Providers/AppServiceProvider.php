<?php

namespace App\Providers;

use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\Invoice;
use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\TechnicalReport;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Observer audit didaftarkan terpusat di sini agar model milik modul lain
     * tidak perlu diubah, tetapi setiap perubahan tetap tercatat ke
     * ACTIVITY_LOGS (PT-10, AD-14, DR-16).
     */
    public function boot(): void
    {
        Owner::observe(ActivityLogObserver::class);
        Ship::observe(ActivityLogObserver::class);
        Opnam::observe(ActivityLogObserver::class);
        OpnamItem::observe(ActivityLogObserver::class);
        Billing::observe(ActivityLogObserver::class);
        BillingAdjustment::observe(ActivityLogObserver::class);
        Tariff::observe(ActivityLogObserver::class);
        Invoice::observe(ActivityLogObserver::class);
        TechnicalReport::observe(ActivityLogObserver::class);
    }
}
