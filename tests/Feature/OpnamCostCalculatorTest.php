<?php

use App\Services\OpnamCostCalculator;

test('item dengan semua faktor bernilai 1 menghasilkan subtotal volume kali unit price', function () {
    $calculator = new OpnamCostCalculator();

    expect($calculator->subtotalItem(10, 100000))->toBe(1000000.0)
        ->and($calculator->faktorKetebalan(12))->toBe(1.0)
        ->and($calculator->faktorKompleksitas('high'))->toBe(1.0);
});

test('contoh kalkulasi PRD 3.10.4 menghasilkan 1.320.000', function () {
    config()->set('opnam.faktor_ketebalan.12', 1.2);
    config()->set('opnam.faktor_kompleksitas.tinggi', 1.1);

    $calculator = new OpnamCostCalculator();

    expect($calculator->faktorKetebalan(12))->toBe(1.2)
        ->and($calculator->faktorKompleksitas('tinggi'))->toBe(1.1)
        ->and($calculator->subtotalItem(10, 100000, 1.2, 1.1))->toBe(1320000.0);
});

test('total biaya sama dengan jumlah subtotal seluruh item', function () {
    $calculator = new OpnamCostCalculator();

    expect($calculator->totalBiaya([
        ['subtotal' => 1250.50],
        ['subtotal' => 749.50],
        ['subtotal' => 3000],
    ]))->toBe(5000.0);
});

test('pembulatan mengikuti konfigurasi', function () {
    config()->set('opnam.pembulatan', 2);

    $calculator = new OpnamCostCalculator();

    expect($calculator->subtotalItem(3, 1000.005))->toBe(3000.02);
});
