<?php

use App\Support\Money;

test('taka amounts use lakh and crore grouping', function (float $amount, string $expected) {
    expect(Money::lakhGrouping($amount))->toBe($expected);
})->with([
    [0, '0.00'],
    [950, '950.00'],
    [14500, '14,500.00'],
    [245000, '2,45,000.00'],
    [2950000, '29,50,000.00'],
    [11500000.5, '1,15,00,000.50'],
]);
