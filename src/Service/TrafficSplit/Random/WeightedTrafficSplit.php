<?php

namespace App\Service\TrafficSplit\Random;

use App\Api\PaymentGatewayInterface;
use App\Api\Service\TrafficSplitInterface;
use App\Dto\Payment;
use InvalidArgumentException;

final class WeightedTrafficSplit implements TrafficSplitInterface
{
    private array $items = [];
    private array $cumulative = [];
    private int $totalWeight;

    public function __construct(array $gatewaysWithWeights)
    {
        if (count($gatewaysWithWeights) === 0) {
            throw new InvalidArgumentException('At least one gateway required');
        }

        $total = 0;
        foreach ($gatewaysWithWeights as $item) {
            if (!isset($item['gateway']) || !isset($item['weight'])) {
                throw new InvalidArgumentException('Each item must have gateway and weight');
            }
            /** @var PaymentGatewayInterface $g */
            $gateway = $item['gateway'];
            $weight = (int)$item['weight'];
            if ($weight < 0) {
                throw new InvalidArgumentException('Weight must be non-negative');
            }
            $this->items[] = ['gateway' => $gateway, 'weight' => $weight];
            $total += $weight;
        }

        if ($total <= 0) {
            throw new InvalidArgumentException('Total weight must be greater than zero');
        }

        $this->totalWeight = $total;

        $cumulative = [];
        $cursor = 0;
        foreach ($this->items as $idx => $item) {
            $cursor += $item['weight'];
            $cumulative[$idx] = $cursor;
        }
        $this->cumulative = $cumulative;
    }

    public function handlePayment(Payment $payment): PaymentGatewayInterface
    {
        $pick = random_int(1, $this->totalWeight);

        foreach ($this->cumulative as $idx => $cum) {
            if ($pick <= $cum) {
                $gateway = $this->items[$idx]['gateway'];
                $gateway->handlePayment($payment);
                return $gateway;
            }
        }

        $gateway = $this->items[array_key_last($this->items)]['gateway'];
        $gateway->handlePayment($payment);
        return $gateway;
    }
}
