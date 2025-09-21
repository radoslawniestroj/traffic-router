<?php

namespace App\Service\TrafficSplit\RoundRobin;

use App\Api\PaymentGatewayInterface;
use App\Api\Service\TrafficSplitInterface;
use App\Dto\Payment;
use InvalidArgumentException;

final class EqualTrafficSplit implements TrafficSplitInterface
{
    private array $gateways;
    private int $index = 0;

    public function __construct(array $gateways)
    {
        if (count($gateways) === 0) {
            throw new InvalidArgumentException('At least one gateway is required');
        }

        $this->gateways = array_values($gateways);
    }

    public function handlePayment(Payment $payment): PaymentGatewayInterface
    {
        $gateway = $this->gateways[$this->index];
        $gateway->handlePayment($payment);
        $this->index = ($this->index + 1) % count($this->gateways);

        return $gateway;
    }
}
