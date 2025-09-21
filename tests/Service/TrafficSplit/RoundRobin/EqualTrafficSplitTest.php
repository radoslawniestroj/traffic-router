<?php

namespace App\Tests\Service\TrafficSplit\RoundRobin;

use App\Dto\Payment;
use App\Gateway\FakeGateway;
use App\Service\TrafficSplit\RoundRobin\EqualTrafficSplit;
use PHPUnit\Framework\TestCase;

final class EqualTrafficSplitTest extends TestCase
{
    public function testEqualDistributionApproximate(): void
    {
        $gateway1 = new FakeGateway('gateway1');
        $gateway2 = new FakeGateway('gateway2');
        $gateway3 = new FakeGateway('gateway3');
        $gateway4 = new FakeGateway('gateway4');
        $gateways = [$gateway1, $gateway2, $gateway3, $gateway4];

        $split = new EqualTrafficSplit($gateways);

        $runs = 1000;
        for ($i = 0; $i < $runs; $i++) {
            $payment = new Payment(1.0, 'EUR');
            $split->handlePayment($payment);
        }

        $expected = $runs / 4;
        $tolerance = (int)($runs * 0.06);

        foreach ($gateways as $gateway) {
            $this->assertGreaterThanOrEqual($expected - $tolerance, $gateway->getTrafficLoad());
            $this->assertLessThanOrEqual($expected + $tolerance, $gateway->getTrafficLoad());
        }
    }
}
