<?php

namespace App\Service\RoundRobin;

use App\Dto\Payment;
use App\Contract\PaymentGatewayInterface;
use App\Service\TrafficSplitInterface;
use InvalidArgumentException;

final class EqualTrafficSplit implements TrafficSplitInterface
{
    private array $gateways;

    private int $index = 0;

    /**
     * @param PaymentGatewayInterface[] $gateways
     */
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

        // move pointer to next gateway (wrap around)
        $this->index = ($this->index + 1) % count($this->gateways);

        return $gateway;
    }
}
