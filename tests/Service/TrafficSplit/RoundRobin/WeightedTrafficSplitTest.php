<?php

namespace App\Tests\Service\TrafficSplit\RoundRobin;

use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\TrafficSplit\RoundRobin\WeightedTrafficSplit;
use PHPUnit\Framework\TestCase;

final class WeightedTrafficSplitTest extends TestCase
{
    public function testWeightedDistributionApproximate(): void
    {
        $gateway1 = new FakeGateway('gateway1');
        $gateway2 = new FakeGateway('gateway2');
        $gateway3 = new FakeGateway('gateway3');

        $split = new WeightedTrafficSplit([
            ['gateway' => $gateway1, 'weight' => 75],
            ['gateway' => $gateway2, 'weight' => 10],
            ['gateway' => $gateway3, 'weight' => 15]
        ]);

        $runs = 1000;
        for ($i = 0; $i < $runs; $i++) {
            $payment = new Payment(1.0, 'EUR');
            $split->handlePayment($payment);
        }

        $exp1 = (int)round($runs * 0.75);
        $exp2 = (int)round($runs * 0.10);
        $exp3 = (int)round($runs * 0.15);

        $tol = (int)($runs * 0.07);

        $this->assertGreaterThanOrEqual($exp1 - $tol, $gateway1->getTrafficLoad(), 'g1 below expected');
        $this->assertLessThanOrEqual($exp1 + $tol, $gateway1->getTrafficLoad(), 'g1 above expected');

        $this->assertGreaterThanOrEqual($exp2 - $tol, $gateway2->getTrafficLoad(), 'g2 below expected');
        $this->assertLessThanOrEqual($exp2 + $tol, $gateway2->getTrafficLoad(), 'g2 above expected');

        $this->assertGreaterThanOrEqual($exp3 - $tol, $gateway3->getTrafficLoad(), 'g3 below expected');
        $this->assertLessThanOrEqual($exp3 + $tol, $gateway3->getTrafficLoad(), 'g3 above expected');
    }
}
