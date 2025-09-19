<?php

namespace App\Tests\Service;

use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\WeightedTrafficSplit;
use PHPUnit\Framework\TestCase;

final class WeightedTrafficSplitTest extends TestCase
{
    public function testWeightedDistributionApproximate(): void
    {
        $g1 = new FakeGateway('g1');
        $g2 = new FakeGateway('g2');
        $g3 = new FakeGateway('g3');

        // weights: 75, 10, 15
        $split = new WeightedTrafficSplit([
            ['gateway' => $g1, 'weight' => 75],
            ['gateway' => $g2, 'weight' => 10],
            ['gateway' => $g3, 'weight' => 15],
        ]);

        $runs = 1000;
        for ($i = 0; $i < $runs; $i++) {
            $payment = new Payment(1.0, 'EUR');
            $split->handlePayment($payment);
        }

        // expected counts
        $exp1 = (int)round($runs * 0.75); // 750
        $exp2 = (int)round($runs * 0.10); // 100
        $exp3 = (int)round($runs * 0.15); // 150

        // tolerance: 7% of runs
        $tol = (int)($runs * 0.07);

        $this->assertGreaterThanOrEqual($exp1 - $tol, $g1->getTrafficLoad(), 'g1 below expected');
        $this->assertLessThanOrEqual($exp1 + $tol, $g1->getTrafficLoad(), 'g1 above expected');

        $this->assertGreaterThanOrEqual($exp2 - $tol, $g2->getTrafficLoad(), 'g2 below expected');
        $this->assertLessThanOrEqual($exp2 + $tol, $g2->getTrafficLoad(), 'g2 above expected');

        $this->assertGreaterThanOrEqual($exp3 - $tol, $g3->getTrafficLoad(), 'g3 below expected');
        $this->assertLessThanOrEqual($exp3 + $tol, $g3->getTrafficLoad(), 'g3 above expected');
    }
}
