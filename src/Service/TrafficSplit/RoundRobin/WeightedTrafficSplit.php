<?php

namespace App\Service\TrafficSplit\RoundRobin;

use App\Api\PaymentGatewayInterface;
use App\Api\Service\TrafficSplitInterface;
use App\Dto\Payment;
use InvalidArgumentException;

final class WeightedTrafficSplit implements TrafficSplitInterface
{
    private array $sequence = [];
    private int $index = 0;

    public function __construct(array $gatewaysWithWeights)
    {
        if (count($gatewaysWithWeights) === 0) {
            throw new InvalidArgumentException('At least one gateway is required');
        }

        foreach ($gatewaysWithWeights as $item) {
            if (!isset($item['gateway']) || !isset($item['weight'])) {
                throw new InvalidArgumentException('Each item must have gateway and weight');
            }

            /** @var PaymentGatewayInterface $gateway */
            $gateway = $item['gateway'];
            $weight = (int)$item['weight'];
            if ($weight <= 0) {
                throw new InvalidArgumentException('Weight must be positive');
            }

            for ($i = 0; $i < $weight; $i++) {
                $this->sequence[] = $gateway;
            }
        }

        if (count($this->sequence) === 0) {
            throw new InvalidArgumentException('No gateways added to sequence');
        }
    }

    public function handlePayment(Payment $payment): PaymentGatewayInterface
    {
        $gateway = $this->sequence[$this->index];
        $gateway->handlePayment($payment);

        $this->index = ($this->index + 1) % count($this->sequence);

        return $gateway;
    }
}
