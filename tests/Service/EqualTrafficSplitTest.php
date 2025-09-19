<?php

namespace App\Tests\Service;

use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\EqualTrafficSplit;
use PHPUnit\Framework\TestCase;

final class EqualTrafficSplitTest extends TestCase
{
    public function testEqualDistributionApproximate(): void
    {
        $g1 = new FakeGateway('g1');
        $g2 = new FakeGateway('g2');
        $g3 = new FakeGateway('g3');
        $g4 = new FakeGateway('g4');

        $split = new EqualTrafficSplit([$g1, $g2, $g3, $g4]);

        $runs = 1000;
        for ($i = 0; $i < $runs; $i++) {
            $payment = new Payment(1.0, 'EUR');
            $split->handlePayment($payment);
        }

        // expect roughly 25% each: tolerance 6% (i.e. +-60 counts)
        $expected = $runs / 4;
        $tolerance = (int)($runs * 0.06);

        foreach ([$g1, $g2, $g3, $g4] as $g) {
            $this->assertGreaterThanOrEqual($expected - $tolerance, $g->getTrafficLoad());
            $this->assertLessThanOrEqual($expected + $tolerance, $g->getTrafficLoad());
        }
    }
}
