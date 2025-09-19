<?php

namespace App\Service\TrafficSplit\Random;

use App\Api\PaymentGatewayInterface;
use App\Api\Service\TrafficSplitInterface;
use App\Dto\Payment;
use InvalidArgumentException;

final class EqualTrafficSplit implements TrafficSplitInterface
{
    private array $gateways;

    public function __construct(array $gateways)
    {
        if (count($gateways) === 0) {
            throw new InvalidArgumentException('At least one gateway required');
        }

        $this->gateways = array_values($gateways);
    }

    public function handlePayment(Payment $payment): PaymentGatewayInterface
    {
        $index = random_int(0, count($this->gateways) - 1);
        $gateway = $this->gateways[$index];
        $gateway->handlePayment($payment);

        return $gateway;
    }
}
