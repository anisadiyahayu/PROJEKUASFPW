<?php

use App\Models\Tariff;
use Database\Seeders\MaterialSpecSeeder;
use Database\Seeders\TariffSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\UnitSeeder;
use Database\Seeders\WorkCategorySeeder;
use Database\Seeders\WorkTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('TariffSeeder inserts verified tariff pairs idempotently and preserves existing tariffs', function () {
    $this->seed(UserSeeder::class);
    $this->seed(UnitSeeder::class);
    $this->seed(WorkCategorySeeder::class);
    $this->seed(WorkTypeSeeder::class);
    $this->seed(MaterialSpecSeeder::class);

    $this->seed(TariffSeeder::class);
    expect(Tariff::count())->toBe(81);

    $existing = Tariff::query()->where('effective_from', '2019-01-01')->firstOrFail();
    $originalPrice = $existing->price;

    $this->seed(TariffSeeder::class);

    expect(Tariff::count())->toBe(81)
        ->and(Tariff::findOrFail($existing->id)->price)->toBe($originalPrice)
        ->and(Tariff::query()->whereDate('effective_from', '2019-01-01')->count())->toBe(81);
});
