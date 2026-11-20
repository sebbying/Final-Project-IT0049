<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/Libraries/Money.php';
use App\Libraries\Money;

$checks = 0;
function same(mixed $actual, mixed $expected): void
{
    global $checks;
    if ($actual !== $expected) { throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)); }
    $checks++;
}
same(Money::total('9.99', 3), '29.97');
same(Money::total('0.01', 1000000), '10000.00');
same(Money::cents('12.5'), 1250);
same(Money::decimal(9999999999), '99999999.99');
same(Money::total('99999999.99', 1), '99999999.99');
foreach (['-1', '0', '1.001', '1e2', 'NaN', '100000000.00', '', ' 9.99'] as $bad) {
    try { Money::cents($bad); throw new RuntimeException('Invalid price was accepted: ' . $bad); }
    catch (InvalidArgumentException $e) { $checks++; }
}
foreach ([['1.00', 0], ['1.00', -1], ['99999999.99', 2], ['0.01', 1000001]] as [$price, $quantity]) {
    try { Money::total($price, $quantity); throw new RuntimeException('Invalid total was accepted.'); }
    catch (InvalidArgumentException $e) { $checks++; }
}
echo "PASS: $checks money and boundary checks.\n";
