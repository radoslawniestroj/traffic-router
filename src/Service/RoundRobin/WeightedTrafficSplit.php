<?php

namespace App\Service\RoundRobin;

use App\Dto\Payment;
use App\Contract\PaymentGatewayInterface;
use App\Service\TrafficSplitInterface;
use InvalidArgumentException;

final class WeightedTrafficSplit implements TrafficSplitInterface
{
    private array $sequence = [];

    private int $index = 0;

    /**
     * @param array<int, array{gateway: PaymentGatewayInterface, weight: int}> $gatewaysWithWeights
     *        np. [['gateway'=>$g1,'weight'=>3], ['gateway'=>$g2,'weight'=>1]]
     */
    public function __construct(array $gatewaysWithWeights)
    {
        if (count($gatewaysWithWeights) === 0) {
            throw new InvalidArgumentException('At least one gateway is required');
        }

        foreach ($gatewaysWithWeights as $item) {
            if (!isset($item['gateway']) || !isset($item['weight'])) {
                throw new InvalidArgumentException('Each item must have gateway and weight');
            }
            /** @var PaymentGatewayInterface $g */
            $g = $item['gateway'];
            $w = (int)$item['weight'];
            if ($w <= 0) {
                throw new InvalidArgumentException('Weight must be positive');
            }

            for ($i = 0; $i < $w; $i++) {
                $this->sequence[] = $g;
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

        // move pointer to next gateway (wrap around)
        $this->index = ($this->index + 1) % count($this->sequence);

        return $gateway;
    }
}
